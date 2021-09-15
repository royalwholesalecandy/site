<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;


class Warehouse extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_advancedstock_warehouse', 'w_id');
    }

    public function getSkuCount($warehouseId)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('bms_advancedstock_warehouse_item'), array(new \Zend_Db_Expr('COUNT(DISTINCT wi_product_id) as total')))
            ->where('wi_warehouse_id = ' .$warehouseId)
            ->where('wi_physical_quantity > 0');
        $result = $this->getConnection()->fetchOne($select);
        if (!$result)
            $result = 0;
        return $result;
    }

    public function getProductsCount($warehouseId)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('bms_advancedstock_warehouse_item'), array(new \Zend_Db_Expr('SUM(wi_physical_quantity) as total')))
            ->where('wi_warehouse_id = ' .$warehouseId);
        $result = $this->getConnection()->fetchOne($select);
        if (!$result)
            $result = 0;
        return $result;
    }

}
