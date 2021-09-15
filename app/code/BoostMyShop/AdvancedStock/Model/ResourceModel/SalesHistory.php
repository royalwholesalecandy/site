<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;


class SalesHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_advancedstock_sales_history', 'sh_id');
    }

    public function updateForProductWarehouse($warehouseItemId, $ranges)
    {

        $this->getConnection()->delete($this->getMainTable(), 'sh_warehouse_item_id='.$warehouseItemId);

        $data = ['sh_warehouse_item_id' => $warehouseItemId, 'sh_range_1' => 0, 'sh_range_2' => 0, 'sh_range_3' => 0];

        for($i=1;$i<=3;$i++)
        {
            $fromDate = date('Y-m-d', time() - (3600 * 24 * 7) * $ranges[$i - 1]);
            $data['sh_range_'.$i] = $this->calculateHistory($warehouseItemId, $fromDate);
        }

        $this->getConnection()->insert($this->getMainTable(), $data);

        return $this;
    }

    public function calculateHistory($warehouseItemId, $fromDate)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('sales_order_item'), array(new \Zend_Db_Expr('SUM(qty_invoiced) as total')))
            ->join(
                ['oi' => $this->getTable('bms_advancedstock_extended_sales_flat_order_item')],
                'item_id = esfoi_order_item_id',
                []
                )
            ->join(
                ['wi' => $this->getTable('bms_advancedstock_warehouse_item')],
                'esfoi_warehouse_id = wi_warehouse_id and product_id = wi_product_id',
                []
            )
            ->where('wi_id = ' .$warehouseItemId)
            ->where('created_at >= "' .$fromDate.'"');
        $result = $this->getConnection()->fetchOne($select);
        if (!$result)
            $result = 0;
        return $result;
    }
}
