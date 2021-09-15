<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Routing\Store;


class Warehouse extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_advancedstock_routing_store_warehouse', 'rsw_id');
    }

    public function loadByStoreWarehouse($object, $websiteId, $groupId, $storeId, $warehouseId)
    {
        $connection = $this->getConnection();

        $select = $this->getConnection()->select()->from($this->getTable('bms_advancedstock_routing_store_warehouse'))->where('rsw_website_id = '.$websiteId.' and rsw_group_id = '.$groupId.' and rsw_store_id = '.$storeId.' and rsw_warehouse_id = '.$warehouseId);
        $wrsId = $connection->fetchOne($select);
        if ($wrsId) {
            return $this->load($object, $wrsId);
        }
        else
        {
            $object->setrsw_website_id($websiteId);
            $object->setrsw_group_id($groupId);
            $object->setrsw_store_id($storeId);
            $object->setrsw_warehouse_id($warehouseId);
            return $object;
        }
    }

}
