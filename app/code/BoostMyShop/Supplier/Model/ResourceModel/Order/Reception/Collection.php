<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Order\Reception;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\Order\Reception', 'BoostMyShop\Supplier\Model\ResourceModel\Order\Reception');
    }

    public function addOrderFilter($orderId)
    {
        $this->getSelect()->where("por_po_id = ".$orderId);
        return $this;
    }


}
