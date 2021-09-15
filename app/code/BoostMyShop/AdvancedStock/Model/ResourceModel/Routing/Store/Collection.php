<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Routing\Store;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\Routing\Store', 'BoostMyShop\AdvancedStock\Model\ResourceModel\Routing\Store');
    }

}
