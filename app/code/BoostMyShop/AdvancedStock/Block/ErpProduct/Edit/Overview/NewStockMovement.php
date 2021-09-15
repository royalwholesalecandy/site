<?php

namespace BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Overview;

class NewStockMovement extends \Magento\Backend\Block\Template
{
    protected $_template = 'ErpProduct/Edit/Overview/NewStockMovement.phtml';

    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\StockMovement\Category $categoryHelper,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_categoryHelper = $categoryHelper;
        $this->_coreRegistry = $coreRegistry;
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getWarehouses()
    {
        return $this->_warehouseCollectionFactory->create()->addActiveFilter();
    }

    public function getCategories()
    {
        return $this->_categoryHelper->getAll();
    }

    public function isHidden()
    {
        $excludedProductTypes = ['configurable', 'bundle','grouped'];
        if (in_array($this->getProduct()->getTypeId(), $excludedProductTypes))
            return true;
        else
            return false;
    }

}