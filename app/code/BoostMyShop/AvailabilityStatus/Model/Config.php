<?php

namespace BoostMyShop\AvailabilityStatus\Model;

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

    public function getSetting($path, $storeId = 0, $scope = 'store')
    {
        if (!$scope)
            $scope = 'default';
        return $this->_scopeConfig->getValue('availabilitystatus/'.$path, $scope, $storeId);
    }

    public function getGlobalSetting($path, $storeId = 0)
    {
        return $this->_scopeConfig->getValue($path, 'store', $storeId);
    }


}