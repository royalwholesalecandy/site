<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;


class RemoveAdditionnalStockItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('', '');
    }


    public function deleteRecords()
    {
        $table = $this->getTable('cataloginventory_stock_item');
        $this->getConnection()->delete($table, 'website_id > 0');
        return $this;
    }


}
