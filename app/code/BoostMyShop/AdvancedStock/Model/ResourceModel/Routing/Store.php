<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Routing;


class Store extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_advancedstock_routing_store', 'rs_id');
    }

    public function loadByStore($object, $websiteId, $groupId, $storeId)
    {
        $connection = $this->getConnection();

        $select = $this->getConnection()->select()->from($this->getTable('bms_advancedstock_routing_store'))->where('rs_website_id = '.$websiteId.' and rs_group_id = '.$groupId.' and rs_store_id = '.$storeId);
        $rsId = $connection->fetchOne($select);
        if ($rsId) {
            return $this->load($object, $rsId);
        }
        else
        {
            $object->setrs_website_id($websiteId);
            $object->setrs_group_id($groupId);
            $object->setrs_store_id($storeId);
            return $object;
        }

    }
}
