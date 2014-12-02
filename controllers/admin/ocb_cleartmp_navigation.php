<?php

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
        return oxRegistry::getConfig()->getShopConfVar('blDevMode', null, 'module:ocb_cleartmp');
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
     * @return null
     */
    public function deleteFiles()
    {
        $oConf   = oxRegistry::getConfig();
        $option  = $oConf->getRequestParameter('clearoption');
        $sTmpDir = realpath($oConf->getShopConfVar('sCompileDir'));
        
        switch ($option) {
            case 'smarty':
                $aFiles = glob($sTmpDir.'/smarty/*.php');
                break;
            case 'staticcache':
                $aFiles = glob($sTmpDir.'/ocb_cache/*.json');
                break;
            case 'language':
                oxRegistry::get('oxUtils')->resetLanguageCache();
                break;
            case 'database':
                $aFiles = glob($sTmpDir.'/*{_allfields_,i18n,_aLocal,allviews}*', GLOB_BRACE);
                break;
            case 'complete':
                $aFiles = glob($sTmpDir.'/*{.php,.txt}', GLOB_BRACE);
                $aFiles = array_merge($aFiles, glob($sTmpDir.'/smarty/*.php'));
                $aFiles = array_merge($aFiles, glob($sTmpDir.'/ocb_cache/*.json'));
                if ($this->isPictureCache()) {
                    $aFiles = array_merge($aFiles, glob($oConf->getPictureDir(false) . 'generated/*'));
                }
                if ($this->isEEVersion()) {
                    $this->_clearContentCache();
                }
                break;
            case 'seo':
                $aFiles = glob($sTmpDir.'/*seo.txt');
                break;
            case 'picture':
                $aFiles = glob($oConf->getPictureDir(false) . 'generated/*');
                break;
            case 'content':
                $this->_clearContentCache();
                break;
            case 'allMods':
                $this->removeAllModuleEntriesFromDb();
                $aFiles = glob($sTmpDir.'/*{.php,.txt}', GLOB_BRACE);
                $aFiles = array_merge($aFiles, glob($sTmpDir.'/smarty/*.php'));
                $aFiles = array_merge($aFiles, glob($sTmpDir.'/ocb_cache/*.json'));
                return;
            case 'none':
            default:
                return;
        }
        
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
     * @param $dir
     *
     * @return bool
     */
    public function _clearDir($dir)
    {
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
}
