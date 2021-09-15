<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Invoice;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\Invoice', 'BoostMyShop\Supplier\Model\ResourceModel\Invoice');
    }

}
