<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;


class StockMovementLogs extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_advancedstock_stock_movement_logs', 'id');
    }

    public function prune()
    {

        $mainTable = $this->getTable('bms_advancedstock_stock_movement_logs');
        $smTable = $this->getTable('bms_advancedstock_stock_movement');

        $to = date('Y-m-d H:i:s', time() - 3600 * 24 * 45);

        $sql = 'delete '.$mainTable.'.*
                from '.$mainTable.'
                join '.$smTable.' on ('.$mainTable.'.sm_id = '.$smTable.'.sm_id)
                where sm_created_at < "'.$to.'"
        ';

        $this->getConnection()->query($sql);

        return $this;
    }

}