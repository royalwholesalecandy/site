<?php

namespace BoostMyShop\AdvancedStock\Model\Routing\Store;


class Warehouse extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\Routing\Store\Warehouse');
    }

    public function getDefaultItem($warehouseId)
    {
        $this->setrsw_website_id(0);
        $this->setrsw_group_id(0);
        $this->setrsw_store_id(0);
        $this->setrsw_warehouse_id($warehouseId);
        $this->setrsw_use_default(0);
        $this->setrsw_use_for_sales(1);
        $this->setrsw_use_for_shipments(1);
        $this->setrsw_priority(1);

        return $this;
    }

    public function loadByStoreWarehouse($websiteId, $groupId, $storeId, $warehouseId)
    {
        $this->_getResource()->loadByStoreWarehouse($this, $websiteId, $groupId, $storeId, $warehouseId);
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }

}
