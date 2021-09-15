<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Supplier;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\Supplier', 'BoostMyShop\Supplier\Model\ResourceModel\Supplier');
    }


    public function toOptionArray()
    {
        $options = array();

        //$this->addFieldToFilter('sup_is_active', 1);
        $this->setOrder('sup_name', 'ASC');
        $collection = $this->load();

        foreach($collection as $item)
        {
            $options[$item->getId()] = $item->getsup_name();
        }

        return $options;
    }

}
