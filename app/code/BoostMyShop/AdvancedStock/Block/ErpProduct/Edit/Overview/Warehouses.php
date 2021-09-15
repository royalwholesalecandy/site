<?php

namespace BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Overview;

class Warehouses extends \Magento\Backend\Block\Template
{
    protected $_template = 'ErpProduct/Edit/Overview/Warehouses.phtml';
    protected $_config;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
        $this->_config = $config;
    }

    public function getWarehouses()
    {
        return $this->_warehouseItemCollectionFactory
            ->create()
            ->addProductFilter($this->getProduct()->getId())
            ->joinWarehouse()
            ;
    }

    public function getUseConfigOptions($defaultValue)
    {
        $options = [];
        $options[1] = __('Default (%1)', $defaultValue);
        $options[0] = __('Custom value');
        return $options;
    }

    public function getDefaultWarningStockLevel()
    {
        return $this->_config->getDefaultWarningStockLevel();
    }

    public function getDefaultIdealStockLevel()
    {
        return $this->_config->getDefaultIdealStockLevel();
    }

}