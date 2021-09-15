<?php

namespace BoostMyShop\Organizer\Model;

class Config
{
    protected $_scopeConfig;
    protected $_moduleManager;

    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->_scopeConfig = $scopeConfig;
        $this->_moduleManager = $moduleManager;
    }

    public function getSetting($path, $storeId = 0)
    {
        return $this->_scopeConfig->getValue('organizer/'.$path, 'store', $storeId);
    }

    public function getGlobalSetting($path, $storeId = 0)
    {
        return $this->_scopeConfig->getValue($path, 'store', $storeId);
    }

    public function decodeSetting($setting)
    {
        if ($this->isJson($setting))
            return json_decode($setting, true);
        else
            return unserialize($setting);
    }

    protected function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

}