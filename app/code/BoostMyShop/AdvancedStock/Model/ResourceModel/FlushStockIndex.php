<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;


class FlushStockIndex extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('', '');
    }


    public function flush()
    {
        $table = $this->getTable('cataloginventory_stock_status');
        $this->getConnection()->delete($table, '1');
        return $this;
    }


}
