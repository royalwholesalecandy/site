<?php

namespace BoostMyShop\OrderPreparation\Model;

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


    public function getOrderStateComplete(){
        return $this->_scopeConfig->getValue('orderpreparation/packing/order_state_complete');
    }

    public function getOrderStateProcessing(){
        return $this->_scopeConfig->getValue('orderpreparation/packing/order_state_processing');
    }

    public function getChangeOrderStatusAfterPacking()
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/change_order_status_after_packing');
    }

    public function getSetting($path, $storeId = 0)
    {
        return $this->_scopeConfig->getValue('orderpreparation/'.$path, 'store', $storeId);
    }

    public function getGlobalSetting($path, $storeId = 0)
    {
        return $this->_scopeConfig->getValue($path, 'store', $storeId);
    }

    public function getBarcodeAttribute()
    {
        return $this->_scopeConfig->getValue('orderpreparation/attributes/barcode_attribute');
    }

    public function getLocationAttribute()
    {
        return $this->_scopeConfig->getValue('orderpreparation/attributes/shelflocation_attribute');
    }

    public function getOrderStatusesForTab($tab)
    {
        $statuses = explode(',', $this->_scopeConfig->getValue('orderpreparation/status_mapping/'.$tab));
        return $statuses;
    }

    public function getAllowPartialPacking()
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/allow_partial');
    }

    public function getCreateInvoice()
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/create_invoice');
    }

    public function getCreateShipment()
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/create_shipment');
    }

    public function includeInvoiceInDownloadDocuments()
    {
        return $this->_scopeConfig->getValue('orderpreparation/download/invoice');
    }

    public function includeShipmentInDownloadDocuments()
    {
        return $this->_scopeConfig->getValue('orderpreparation/download/shipment');
    }

    public function getPdfPickingLayout()
    {
        return $this->_scopeConfig->getValue('orderpreparation/picking/pdf_layout');
    }

    public function isOrderEditorEnabled()
    {
        return $this->_scopeConfig->getValue('orderpreparation/order_editor/enabled');
    }

    public function isErpIsInstalled()
    {
        return $this->_moduleManager->isEnabled('BoostMyShop_Erp');
    }

    public function displayCustomOptionsOnPicking()
    {
        return $this->_scopeConfig->getValue('orderpreparation/picking/display_options');
    }

    public function pickingListOnePagePerOrder()
    {
        return $this->_scopeConfig->getValue('orderpreparation/picking/one_page_per_order');
    }

    public function includeGlobalPickingList()
    {
        return $this->_scopeConfig->getValue('orderpreparation/picking/include_global_pickinglist');
    }

    public function getGroupBundleItems()
    {
        return $this->_scopeConfig->getValue('orderpreparation/picking/group_bundle_items');
    }

    public function canEditShippingMethod()
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/can_edit_shipping_method');
    }

}