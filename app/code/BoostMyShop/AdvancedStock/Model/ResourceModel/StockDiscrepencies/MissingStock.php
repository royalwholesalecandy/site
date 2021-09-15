<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\StockDiscrepencies;


class MissingStock extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('', '');
    }


    public function stockExistForWebsite($websiteId)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('cataloginventory_stock'), array(new \Zend_Db_Expr('count(*)')))
            ->where('website_id = '.$websiteId);
        $result = $this->getConnection()->fetchOne($select);
        return (int)$result;
    }


}