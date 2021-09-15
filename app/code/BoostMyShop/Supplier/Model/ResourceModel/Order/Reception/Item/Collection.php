<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Order\Reception\Item;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\Order\Reception\Item', 'BoostMyShop\Supplier\Model\ResourceModel\Order\Reception\Item');
    }

    public function addReceptionFilter($receptionId)
    {
        $this->getSelect()->where("pori_por_id = ".$receptionId);

        return $this;
    }


    public function addOrderProductDetails($orderId = null)
    {
        $this->getSelect()->join($this->getTable('bms_purchase_order_reception'), 'por_id = pori_por_id');
        $this->getSelect()->join($this->getTable('bms_purchase_order'), 'po_id = por_po_id');
        $this->getSelect()->join($this->getTable('bms_purchase_order_product'), 'po_id = pop_po_id and pori_product_id = pop_product_id');

        if ($orderId)
            $this->getSelect()->where('pop_po_id='.$orderId);

        return $this;
    }

    public function addProductFilter($productId)
    {
        $this->getSelect()->where("pop_product_id = ".$productId);
        return $this;
    }


    public function getAllProductIds()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns(new \Zend_Db_Expr('distinct pori_product_id'));

        $result = $this->getConnection()->fetchCol($this->getSelect());

        return $result;
    }

}
