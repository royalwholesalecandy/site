<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\StockDiscrepencies;


class MissingWarehouseItems extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('', '');
    }


    public function getExisting()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('bms_advancedstock_warehouse_item'), array(new \Zend_Db_Expr('distinct concat(wi_warehouse_id, "_", wi_product_id) as item')));
        $result = $this->getConnection()->fetchCol($select);
        return $result;
    }

    public function getRequired()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('bms_advancedstock_stock_movement'), array(new \Zend_Db_Expr('distinct concat(sm_from_warehouse_id, "_", sm_product_id) as item')))
            ->where('sm_from_warehouse_id > 0');
        $result = $this->getConnection()->fetchCol($select);

        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('bms_advancedstock_stock_movement'), array(new \Zend_Db_Expr('distinct concat(sm_to_warehouse_id, "_", sm_product_id) as item')))
            ->where('sm_to_warehouse_id > 0');
        $result2 = $this->getConnection()->fetchCol($select);

        return array_merge($result, $result2);
    }


}
