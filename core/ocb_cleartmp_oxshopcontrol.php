<?php

if (false) {
    class ocb_cleartmp_oxshopcontrol_parent extends oxShopControl
    {
    }
}

class ocb_cleartmp_oxshopcontrol extends ocb_cleartmp_oxshopcontrol_parent
{
    protected function _runOnce()
    {
        $oConf     = oxRegistry::getConfig();
        $blDevMode = $oConf->getShopConfVar('blDevMode', null, 'module:ocb_cleartmp');
        
        
        if ($blDevMode && !$oConf->isProductiveMode()) {
            $sTmpDir = realpath($oConf->getShopConfVar('sCompileDir'));
            $aFiles = glob($sTmpDir.'{/smarty/,/ocb_cache/,/}*{.php,.txt,.json}', GLOB_BRACE);
            if (count($aFiles) > 0) {
                foreach ($aFiles as $file) {
                    @unlink($file);
                }
            }
        }
        parent::_runOnce();
    }

    /**
     * Shows exceptionError page.
     * possible reason: class does not exist etc. --> just redirect to start page.
     *
     * @param $oEx
     */
    protected function _handleSystemException($oEx)
    {
        //possible reason: class does not exist etc. --> just redirect to start page
        if ($this->_isDevelopmentMode()) {
            oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx);
            $this->_process('exceptionError', 'displayExceptionError');
        }
        $oEx->debugOut();
    }

    /**
     * Redirect to start page, in debug mode shows error message.
     *
     * @param $oEx
     */
    protected function _handleCookieException($oEx)
    {
        if ($this->_isDevelopmentMode()) {
            oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx);
        }
    }

    /**
     * Checks if shop is in development mode
     *
     * @return bool
     */
    protected function _isDevelopmentMode()
    {
        return oxRegistry::getConfig()->getShopConfVar('blDevMode', null, 'module:ocb_cleartmp');
    }
}
