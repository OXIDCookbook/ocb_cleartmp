<?php

/**
 * ocb_cleartmp_oxshopcontrol
 *
 * @package   ocb_cleartmp
 * @version   GIT: $Id$ PHP5.4 (16.10.2014)
 * @author    Joscha Krug <krug@marmalade.de>
 * @link      http://blog.marmalade.de
 * @extend    oxShopControl
 *
 */
class ocb_cleartmp_oxshopcontrol extends ocb_cleartmp_oxshopcontrol_parent
{

    /**
     * clears the cache on initialization of Shop
     */
    protected function _runOnce()
    {
        $oConf = oxRegistry::getConfig();
        $blDevMode = $oConf->getShopConfVar('blDevMode', null, 'module:ocb_cleartmp');

        if ($blDevMode && !$oConf->isProductiveMode()) {

            $sTmpDir = realpath($oConf->getShopConfVar('sCompileDir'));
            $aFiles = glob($sTmpDir . '/*{.php,.txt}', GLOB_BRACE);
            $aFiles = array_merge($aFiles, glob($sTmpDir . '/smarty/*.php'));
            $aFiles = array_merge($aFiles, glob($sTmpDir . '/ocb_cache/*.json'));
            if (count($aFiles) > 0) {
                foreach ($aFiles as $file) {
                    @unlink($file);
                }
            }
        }
        parent::_runOnce();
    }
}
