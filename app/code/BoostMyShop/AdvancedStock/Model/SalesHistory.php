<?php

namespace BoostMyShop\AdvancedStock\Model;


class SalesHistory extends \Magento\Framework\Model\AbstractModel
{
    protected $_warehouseItemCollection;
    protected $_config;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollection,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_warehouseItemCollection = $warehouseItemCollection;
        $this->_config = $config;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\SalesHistory');
    }

    public function updateForProduct($productId)
    {
        $collection = $this->_warehouseItemCollection->create()->addProductFilter($productId);
        foreach($collection as $item)
            $this->updateForProductWarehouse($item->getId());
    }

    public function updateForProductWarehouse($warehouseItemId)
    {
        $ranges = [];
        for ($i=1;$i<=3;$i++)
            $ranges[] = $this->_config->getSetting('stock_level/history_range_'.$i);

        $this->_getResource()->updateForProductWarehouse($warehouseItemId, $ranges);

        return $this->load($warehouseItemId, 'sh_warehouse_item_id');
    }

}