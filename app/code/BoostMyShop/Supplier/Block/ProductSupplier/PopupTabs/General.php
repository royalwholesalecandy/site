<?php

namespace BoostMyShop\Supplier\Block\ProductSupplier\PopupTabs;

class General extends \Magento\Backend\Block\Template
{
    protected $_template = 'ProductSupplier/Popup/General.phtml';
    protected $_supplierProductFactory;
    protected $_coreRegistry;
    protected $productSupplier;
    protected $_config;
    protected $_supplier;
    public function __construct(\Magento\Backend\Block\Template\Context $context,
    	\BoostMyShop\Supplier\Model\Supplier\ProductFactory $supplierProductFactory,
        \BoostMyShop\Supplier\Model\Supplier $_supplier,
        \BoostMyShop\Supplier\Model\Config $config,
    	\Magento\Framework\Registry $coreRegistry)
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_supplier = $_supplier;
        $this->_config = $config;
        $this->_supplierProductFactory = $supplierProductFactory;
    }

    public function getProductSupplier(){
    	$this->productSupplier = $this->_supplierProductFactory->create();
    	$supId = $this->_coreRegistry->registry('current_popup_supId');
    	$productId = $this->_coreRegistry->registry('current_popup_productId');
    	if($supId && $productId){
    		$this->productSupplier = $this->productSupplier->loadByProductSupplier($productId, $supId);
    	}
    	return $this->productSupplier;
    }
    public function getSupplier(){
        $supId = $this->_coreRegistry->registry('current_popup_supId');
        $supplier = $this->_supplier->load($supId);
        return $supplier;
    }

    public function isPackQtyEnabled(){
        return $this->_config->getSetting('general/pack_quantity');
    }
}