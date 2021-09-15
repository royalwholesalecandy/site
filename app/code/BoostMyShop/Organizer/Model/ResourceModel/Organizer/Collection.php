<?php

namespace BoostMyShop\Organizer\Model\ResourceModel\Organizer;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Organizer\Model\Organizer', 'BoostMyShop\Organizer\Model\ResourceModel\Organizer');
    }

    public function addObjectFilter($objectType, $objectId)
    {
    	$this->getSelect()->where('o_object_id = '.$objectId.' and o_object_type = "'.$objectType.'"');
        return $this;
    }
}
