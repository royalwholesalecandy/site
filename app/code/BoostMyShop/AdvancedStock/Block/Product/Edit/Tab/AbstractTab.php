<?php
namespace BoostMyShop\AdvancedStock\Block\Product\Edit\Tab;

class AbstractTab extends \Magento\Backend\Block\Template
{
    protected $_coreRegistry;
    protected $_warehouseCollectionFactory;
    protected $_warehouseItemCollectionFactory;
    protected $_categories;
    protected $_config;
    protected $_stockItemCollectionFactory;
    protected $_stockRegistryProvider;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\Registry $coreRegistry,
                                \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
                                \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
                                \BoostMyShop\AdvancedStock\Model\StockMovement\Category $categories,
                                \BoostMyShop\AdvancedStock\Model\Config $config,
                                \Magento\CatalogInventory\Model\StockRegistryProvider $stockRegistryProvider,
                                \BoostMyShop\AdvancedStock\Model\ResourceModel\Stock\Item\CollectionFactory $stockItemCollectionFactory,
                                array $data = [])
    {
        $this->_coreRegistry = $coreRegistry;
        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_categories = $categories;
        $this->_config = $config;
        $this->_stockItemCollectionFactory = $stockItemCollectionFactory;
        $this->_stockRegistryProvider = $stockRegistryProvider;

        parent::__construct($context, $data);
    }

    public function getProduct()
    {
        if ($this->_coreRegistry->registry('advancedstock_current_product'))
            return $this->_coreRegistry->registry('advancedstock_current_product');
        else
            return $this->_coreRegistry->registry('current_product');
    }

    public function getProductId()
    {
        if ($this->getProduct())
            return $this->getProduct()->getId();
        else
            return false;
    }

}