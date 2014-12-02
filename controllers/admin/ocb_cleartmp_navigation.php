<?php

/**
 * ocb_cleartmp_navigation
 *
 * @package   ocb_cleartmp
 * @version   GIT: $Id$ PHP5.4 (16.10.2014)
 * @author    Joscha Krug <krug@marmalade.de>
 * @link      http://blog.marmalade.de
 * @extend    navigation
 *
 */
class ocb_cleartmp_navigation extends ocb_cleartmp_navigation_parent
{

    /**
     * Change the full template as there is no block jet in the header.
     *
     * @return string templatename
     */
    public function render()
    {
        $sTpl = parent::render();

        $this->_aViewData['prodmode'] = oxRegistry::getConfig()->isProductiveMode();

        if ('header.tpl' == $sTpl) {
            return 'ocb_header.tpl';
        } else {
            return $sTpl;
        }
    }

    /**
     * Method that will be called from the frontend
     * and starts the clearing
     *
     * @return null
     */
    public function cleartmp()
    {
        $oConf = oxRegistry::getConfig();
        $sShopId = $oConf->getShopId();

        $blDevMode = 0;
        if (false != $oConf->getRequestParameter('devmode')) {
            $blDevMode = $oConf->getRequestParameter('devmode');
        }
        $oConf->saveShopConfVar('bool', 'blDevMode', $blDevMode, $sShopId, 'module:ocb_cleartmp');

        $this->deleteFiles();

        return;
    }

    /**
     * Check wether the developermode is enabled or not
     *
     * @return bool
     */
    public function isDevMode()
    {
        return oxRegistry::getConfig()->getShopConfVar('blDevMode',null,'module:ocb_cleartmp');
    }

    /**
     * Check if shop is Enterprise Edition
     *
     * @return bool
     */
    public function isEEVersion()
    {
        return ('EE' === $this->getConfig()->getEdition());
    }

    /**
     * Check if picture Cache enabled
     *
     * @return bool
     */
    public function isPictureCache()
    {
        return oxRegistry::getConfig()->getShopConfVar('sPictureClear', null, 'module:ocb_cleartmp');
    }

    /**
     * Method to remove the files from the cache folder
     * and trigger other options
     * depending on the given option
     *
     * @return null
     */
    public function deleteFiles()
    {
        $option = oxRegistry::getConfig()->getRequestParameter('clearoption');
        $sTmpDir = $this->_getTmpDir();

        switch ($option) {
            case 'smarty':
                $aFiles = glob($sTmpDir . '/smarty/*.php');
                break;
            case 'staticcache':
                $aFiles = glob($sTmpDir . '/ocb_cache/*.json');
                break;
            case 'language':
                oxRegistry::get('oxUtils')->resetLanguageCache();
                break;
            case 'database':
                $aFiles = glob($sTmpDir . '/*{_allfields_,i18n,_aLocal,allviews}*', GLOB_BRACE);
                break;
            case 'complete':
                $this->_clearCompleteCache();
                break;
            case 'seo':
                $aFiles = glob($sTmpDir . '/*seo.txt');
                break;
            case 'picture':
                $aFiles = glob(oxRegistry::getConfig()->getPictureDir(false) . 'generated/*');
                break;
            case 'content':
                $this->_clearContentCache();
                break;
            case 'allMods':
                $this->_clearModuleCache();
                break;
        }
        if (is_array($aFiles)) {
            $this->_clearFiles($aFiles);
        }

        return;
    }

    /**
     * get tmp dir
     *
     * @return string
     */
    protected function _getTmpDir()
    {
        return realpath(oxRegistry::getConfig()->getShopConfVar('sCompileDir'));
    }

    /**
     * clears complete cache
     */
    protected function _clearCompleteCache()
    {
        $aFiles = glob($sTmpDir . '/*{.php,.txt}', GLOB_BRACE);
        $aFiles = array_merge($aFiles, glob($sTmpDir . '/smarty/*.php'));
        $aFiles = array_merge($aFiles, glob($sTmpDir . '/ocb_cache/*.json'));
        if ($this->isPictureCache()) {
            $aFiles = array_merge($aFiles, glob(oxRegistry::getConfig()->getPictureDir(false) . 'generated/*'));
        }
        if ($this->isEEVersion()) {
            $this->_clearContentCache();
        }
    }

    /**
     * clears the content Cache
     */
    protected function _clearContentCache()
    {
        /* @var $oCache \oxCache */
        $oCache = oxNew('oxcache');
        $oCache->reset();
        /* @var $oRpBackend \oxCacheBackend */
        $oRpBackend = oxRegistry::get('oxCacheBackend');
        $oRpBackend->flush();
    }

    /**
     * clears module cache
     */
    protected function _clearModuleCache()
    {
        $this->removeAllModuleEntriesFromDb();
        $aFiles = glob($sTmpDir . '/*{.php,.txt}', GLOB_BRACE);
        $aFiles = array_merge($aFiles, glob($sTmpDir . '/smarty/*.php'));
        $aFiles = array_merge($aFiles, glob($sTmpDir . '/ocb_cache/*.json'));
    }

    /**
     * Remove all module entries from the oxConfig table
     * Will only work if the developer mode is enabled.
     */
    protected function removeAllModuleEntriesFromDb()
    {
        if (false != oxRegistry::getConfig()->getRequestParameter('devmode')) {
            oxDb::getDb()->execute('DELETE FROM `oxconfig` WHERE `OXVARNAME` LIKE \'%aMod%\';');
            oxDb::getDb()->execute('DELETE FROM `oxconfig` WHERE `OXVARNAME` LIKE \'%aDisabledModules%\';');
        }
    }

    /**
     * clear files and dirs
     *
     * @param array $aFiles files to clear
     */
    public function _clearFiles(array $aFiles = array())
    {
        if (count($aFiles) > 0) {
            foreach ($aFiles as $file) {
                if (is_file($file)) {
                    @unlink($file);
                } else {
                    $this->_clearDir($file);
                }
            }
        }
    }

    /**
     * clears a directory
     *
     * @param string $dir directory to clear
     *
     * @return bool
     */
    public function _clearDir($dir)
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                if (is_dir("$dir/$file")) {
                    $this->_clearDir("$dir/$file");
                } else {
                    unlink("$dir/$file");
                }
            }

            return rmdir($dir);
        }
    }


}
