<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\ExtendedSalesFlatOrderItem;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ExtendedSalesFlatOrderItem', 'BoostMyShop\AdvancedStock\Model\ResourceModel\ExtendedSalesFlatOrderItem');
    }

    public function joinOrderItem()
    {
        $this->getSelect()->join(
            $this->getTable('sales_order_item'),
            'item_id = esfoi_order_item_id'
        );
        return $this;
    }

    public function joinOpenedOrder()
    {
        $this->getSelect()->join(
            ['so' => $this->getTable('sales_order')],
            'order_id = so.entity_id'
        );
        $this->addFieldToFilter('state', ['nin' => ['complete', 'canceled', 'closed']]);
        return $this;
    }

    public function joinWarehouseItem()
    {
        $this->getSelect()->join(
            $this->getTable('bms_advancedstock_warehouse_item'),
            'product_id = wi_product_id and esfoi_warehouse_id = wi_warehouse_id'
        );
        return $this;
    }

    public function joinWarehouse()
    {
        $this->getSelect()->join(
            $this->getTable('bms_advancedstock_warehouse'),
            'w_id = esfoi_warehouse_id'
        );
        return $this;
    }

    public function addOrderFilter($orderId)
    {
        $this->addFieldToFilter('order_id', $orderId);
        return $this;
    }

    public function addWarehouseFilter($warehouseId)
    {
        $this->addFieldToFilter('esfoi_warehouse_id', $warehouseId);
        return $this;
    }

    public function addProductTypeFilter()
    {
        $this->addFieldToFilter('product_type', ['nin' => ['configurable', 'bundle', 'configurator']]);
        return $this;
    }

    public function addQtyToShipFilter()
    {
        $this->getSelect()->where('esfoi_qty_to_ship > 0');
        return $this;
    }

    public function addNotFullyReservedFilter()
    {
        $this->getSelect()->where('esfoi_qty_to_ship > esfoi_qty_reserved');
        return $this;
    }

    public function getOrderIds()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns(new \Zend_Db_Expr('distinct order_id'));
        return $this->getConnection()->fetchCol($this->getSelect());
    }

    public function getAllOrderItemId()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns(new \Zend_Db_Expr('distinct esfoi_order_item_id'));
        return $this->getConnection()->fetchCol($this->getSelect());
    }

}
