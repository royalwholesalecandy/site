<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Order;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\Order', 'BoostMyShop\Supplier\Model\ResourceModel\Order');
    }

    public function addSupplierFilter($supplierId)
    {
        $this->getSelect()->where("po_sup_id = ".$supplierId);
        return $this;
    }

    public function addStatusFilter($status)
    {
        $this->getSelect()->where("po_status = '".$status."'");
        return $this;
    }

    public function getOrderCount()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns('count(*) as total_records');
        $result = $this->getConnection()->fetchOne($this->getSelect());
        return (max($result, 0));
    }

}
