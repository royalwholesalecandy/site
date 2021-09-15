<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\CarrierTemplate', 'BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate');
    }

    public function addActiveFilter()
    {
        $this->getSelect()->where("ct_disabled = 0");

        return $this;
    }

    public function addShippingMethodFilter($shippingMethod)
    {
        $this->getSelect()->where("ct_shipping_methods like '%".$shippingMethod."%'");
        return $this;
    }

}
