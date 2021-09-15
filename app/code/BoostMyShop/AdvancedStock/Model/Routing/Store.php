<?php

namespace BoostMyShop\AdvancedStock\Model\Routing;


class Store extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\Routing\Store');
    }

    public function getDefaultItem()
    {
        $this->setrs_website_id(0);
        $this->setrs_group_id(0);
        $this->setrs_store_id(0);
        $this->setrs_use_default(0);
        $this->setrs_routing_mode(\BoostMyShop\AdvancedStock\Model\Routing\Store\Mode::withStockOrderByPriority);

        return $this;
    }

    public function loadByStore($websiteId, $groupId, $storeId)
    {
        $this->_getResource()->loadByStore($this, $websiteId, $groupId, $storeId);
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }

}
