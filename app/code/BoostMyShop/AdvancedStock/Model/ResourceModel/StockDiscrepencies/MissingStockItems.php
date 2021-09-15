<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\StockDiscrepencies;


class MissingStockItems extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('', '');
    }


    public function getExisting()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('cataloginventory_stock_item'), array(new \Zend_Db_Expr('distinct concat(stock_id, "_", website_id, "_", product_id) as item')));
        $result = $this->getConnection()->fetchCol($select);
        return $result;
    }

    public function getRequired()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('catalog_product_entity'), array(new \Zend_Db_Expr('distinct concat(stock_id, "_", website_id, "_", entity_id) as item')))
            ->from($this->getTable('cataloginventory_stock'), [])
            ->where('type_id in ("simple","configurable","bundle","grouped")');
            ;

        $result = $this->getConnection()->fetchCol($select);

        return $result;
    }


}
