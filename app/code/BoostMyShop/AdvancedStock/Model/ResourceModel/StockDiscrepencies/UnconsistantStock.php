<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\StockDiscrepencies;


class UnconsistantStock extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('', '');
    }


    public function getStocks()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('cataloginventory_stock'), array('*'));
        return $this->getConnection()->fetchAll($select);
    }


}