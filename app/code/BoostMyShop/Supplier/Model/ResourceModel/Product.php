<?php

namespace BoostMyShop\Supplier\Model\ResourceModel;


class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('', 'entity_id');
    }

    public function productIsDeleted($productId)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('catalog_product_entity'), array(new \Zend_Db_Expr('COUNT(*) as total')))
            ->where('entity_id = ' .$productId);
        $result = $this->getConnection()->fetchOne($select);
        return ($result == 0);
    }

}
