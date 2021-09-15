<?php

namespace BoostMyShop\UltimateReport\Model;

class Config
{

    protected $_scopeConfig;
    protected $_moduleManager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->_scopeConfig = $scopeConfig;
        $this->_moduleManager = $moduleManager;
    }

    public function getSetting($path, $storeId = 0)
    {
        return $this->_scopeConfig->getValue('ultimatereport/'.$path, 'store', $storeId);
    }

    public function getDashboardReports()
    {
        return explode(',', $this->getSetting('general/dashboard_reports'));
    }


}