<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Routing\Store\Warehouse;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\Routing\Store\Warehouse', 'BoostMyShop\AdvancedStock\Model\ResourceModel\Routing\Store\Warehouse');
    }

}
