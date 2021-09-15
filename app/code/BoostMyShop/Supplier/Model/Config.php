<?php

namespace BoostMyShop\Supplier\Model;

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
        return $this->_scopeConfig->getValue('supplier/'.$path, 'store', $storeId);
    }

    public function getGlobalSetting($path, $storeId = 0)
    {
        return $this->_scopeConfig->getValue($path, 'store', $storeId);
    }

    public function getBarcodeAttribute()
    {
        return $this->_scopeConfig->getValue('supplier/general/barcode_attribute');
    }

    public function getManufacturerAttribute()
    {
        return $this->_scopeConfig->getValue('supplier/general/manufacturer_attribute');
    }

    public function getLocationAttribute()
    {
        return $this->_scopeConfig->getValue('supplier/general/location_attribute');
    }

    public function getNotifyStockQuantity()
    {
        return $this->_scopeConfig->getValue('cataloginventory/item_options/notify_stock_qty');
    }

    public function getExtendedCostMethod()
    {
        return $this->_scopeConfig->getValue('supplier/order_product/extended_cost_method');
    }

    public function updateProductCostAfterReception()
    {
        return $this->_scopeConfig->getValue('supplier/order_product/update_cost');
    }

    public function isErpIsInstalled()
    {
        return $this->_moduleManager->isEnabled('BoostMyShop_Erp');
    }

}