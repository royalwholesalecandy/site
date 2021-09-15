<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse;


class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('bms_advancedstock_warehouse_item', 'wi_id');
    }

    public function loadByProductWarehouse($object, $productId, $warehouseId)
    {
        $connection = $this->getConnection();

        //find item id
        $select = $this->getConnection()->select()->from($this->getTable('bms_advancedstock_warehouse_item'))->where('wi_product_id = '.$productId.' and wi_warehouse_id = '.$warehouseId);
        $itemId = $connection->fetchOne($select);

        return $this->load($object, $itemId);
    }

    public function calculatePhysicalQuantityFromStockMovements($stockId, $productId, $date = null)
    {
        $sql = $this->getConnection()->select()
            ->from(array('tbl_stock_movement' => $this->getTable('bms_advancedstock_stock_movement')),
                array('qty' => 'sum(if(tbl_stock_movement.sm_from_warehouse_id = ' . $stockId . ', -tbl_stock_movement.sm_qty, tbl_stock_movement.sm_qty))'))
            ->where('(tbl_stock_movement.sm_from_warehouse_id = ' . $stockId . ' OR tbl_stock_movement.sm_to_warehouse_id = ' . $stockId . ') AND (sm_from_warehouse_id <> sm_to_warehouse_id)')
            ->where('tbl_stock_movement.sm_product_id = ' . $productId);

        if ($date)
            $sql->where('tbl_stock_movement.sm_created_at <= "'.$date.'"');

        $value = $this->getConnection()->fetchOne($sql);
        return $value;
    }


}
