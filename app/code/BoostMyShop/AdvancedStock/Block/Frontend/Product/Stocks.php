<?php

namespace BoostMyShop\AdvancedStock\Block\Frontend\Product;

use Magento\Framework\View\Element\Template;


class Stocks extends Template
{

    protected $_coreRegistry;
    protected $_warehouseItemCollectionFactory;
    protected $_warehouseFactory;
    protected $_warehouseCollectionFactory;
    protected $_config;


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_config = $config;

        parent::__construct($context, $data);

        $this->setTabTitle();
    }

    /**
     * Get current product id
     *
     * @return null|int
     */
    public function getProductId()
    {
        $product = $this->_coreRegistry->registry('product');
        return $product ? $product->getId() : null;
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Set tab title
     *
     * @return void
     */
    public function setTabTitle()
    {
        $title = __('Stocks');
        $this->setTitle($title);
    }

    public function getWarehouse($warehouseId)
    {
        return $this->_warehouseFactory->create()->load($warehouseId);
    }

    public function getStocks()
    {
        return $this->_warehouseItemCollectionFactory
                        ->create()
                        ->addProductFilter($this->getProductId())
                        ->addInStockFilter()
                        ->joinWarehouse()
                        ->addVisibleOnFrontFilter();
    }

    protected function _toHtml()
    {
        if ($this->_config->displayStocksOnFrontEnd())
            return parent::_toHtml();
    }

}
