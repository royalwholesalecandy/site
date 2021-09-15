<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\InProgress', 'BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress');
    }

    public function addOrderDetails()
    {
        $this->getSelect()->join($this->getTable('sales_order_grid'), 'ip_order_id = entity_id');
        return $this;
    }

    public function getOrderIds($warehouseId = null)
    {
        $this->getSelect()->reset()->from($this->getMainTable(), ['ip_order_id']);
        if ($warehouseId)
            $this->addWarehouseFilter($warehouseId);
        $ids = $this->getConnection()->fetchCol($this->getSelect());
        return $ids;
    }

    public function addUserFilter($userId)
    {
        $this->getSelect()->where('ip_user_id = '.$userId);
        return $this;
    }

    public function addWarehouseFilter($warehouseId)
    {
        $this->getSelect()->where('ip_warehouse_id = '.$warehouseId);
        return $this;
    }

    public function addOrderFilter($orderId)
    {
        $this->getSelect()->where('ip_order_id = '.$orderId);
        return $this;
    }

}
