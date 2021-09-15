<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Product\PendingOrders;


class Collection extends \Magento\Sales\Model\ResourceModel\Order\Item\Collection
{

    public function addProductFilter($productId)
    {
        if (is_object($productId))
            $productId = $productId->getId();

        $this->getSelect()->where('product_id = '.$productId);
        return $this;
    }

    public function addWarehouseFilter($warehouseId)
    {
        $this->getSelect()->where('esfoi_warehouse_id = '.$warehouseId);
        return $this;
    }

    public function addOrderDetails()
    {
        $this->getSelect()->join(
            ['o' => $this->getTable('sales_order')],
            'o.entity_id = order_id',
            [
                'CONCAT(customer_firstname," ", customer_lastname) as order_customer_name',
                'order_date' => 'o.created_at',
                'order_increment_id' => 'o.increment_id',
                'order_id' => 'o.entity_id',
                'order_status' => 'o.status',
                '(esfoi_qty_to_ship) as qty_to_ship'    //backward compatibility / synonym
            ]
        );
        $this->getSelect()->where('(qty_ordered - qty_refunded - qty_shipped - qty_canceled) > 0');

        return $this;
    }

    public function addExtendedDetails()
    {
        $this->getSelect()->join(
            [$this->getTable('bms_advancedstock_extended_sales_flat_order_item')],
            'item_id = esfoi_order_item_id',
            ['esfoi_warehouse_id', 'esfoi_qty_reserved', 'esfoi_qty_to_ship']
        );

        $this->getSelect()->where('esfoi_qty_to_ship > 0');

        return $this;
    }

    public function addStatusesFilter($statuses)
    {
        for($i=0;$i<count($statuses);$i++)
            $statuses[$i] = "'".$statuses[$i]."'";
        if (count($statuses) > 0)
        {
            $this->getSelect()->where('o.status in ('.implode(',', $statuses).')');
        }
        return $this;
    }

    public function getTotalQuantityToShip()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);

        $this->getSelect()->columns(new \Zend_Db_Expr('SUM(qty_ordered - qty_refunded - qty_shipped - qty_canceled) as qty_to_ship'));

        $result = $this->getConnection()->fetchOne($this->getSelect());
        if (!$result)
            $result = 0;
        return $result;
    }

    public function getTotalQuantityReserved()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);

        $this->getSelect()->columns(new \Zend_Db_Expr('SUM(esfoi_qty_reserved) as qty_reserved'));

        $result = $this->getConnection()->fetchOne($this->getSelect());
        if (!$result)
            $result = 0;
        return $result;
    }

    public function getAllProductIds()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns(new \Zend_Db_Expr('distinct product_id'));

        $result = $this->getConnection()->fetchCol($this->getSelect());
        if (!$result)
            $result = 0;
        return $result;
    }

    public function addFieldToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        switch($attribute)
        {
            case 'order_increment_id':
                $attribute = 'increment_id';
                break;
            case 'order_date':
                $attribute = 'created_at';
                break;
            case 'order_status':
                $attribute = 'status';
                break;
        }

        $conditionSql = $this->_getConditionSql($attribute, $condition);
        $this->getSelect()->where($conditionSql);

    }


}
