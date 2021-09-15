<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\Warehouse', 'BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse');
    }

    public function addActiveFilter()
    {
        $this->getSelect()->where('w_is_active = 1');
        return $this;
    }

    public function addFulfillementMethodFilter($fulfillmentMethod)
    {
        $this->getSelect()->where('w_fulfilment_method = "'.$fulfillmentMethod.'"');
        return $this;
    }

}
