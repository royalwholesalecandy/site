<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\Warehouse\Item', 'BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item');
    }

    public function addProductFilter($productId)
    {
        $this->addFieldToFilter('wi_product_id', $productId);
        return $this;
    }

    public function addInStockFilter()
    {
        $this->addFieldToFilter('wi_available_quantity', ['gt' => 0]);
        return $this;
    }

    public function joinWarehouse($addSupplyNeedsFilter = false)
    {
        $this->getSelect()->join(
            $this->getTable('bms_advancedstock_warehouse'),
            'w_id = wi_warehouse_id and w_is_active = 1'
        );

        if ($addSupplyNeedsFilter)
            $this->getSelect()->where('w_use_in_supplyneeds = 1');

        return $this;
    }

    public function addVisibleOnFrontFilter()
    {
        $this->addFieldToFilter('w_display_on_front', 1);
        return $this;
    }

    public function addUnconsistentFilter()
    {
        $conditions = [];
        $conditions[] = "wi_reserved_quantity < wi_physical_quantity and wi_reserved_quantity < wi_quantity_to_ship";
        $conditions[] = "wi_reserved_quantity > wi_quantity_to_ship";
        $conditions[] = "wi_reserved_quantity > wi_physical_quantity";

        $this->getSelect()->where(implode(' OR ', $conditions));

        return $this;
    }

    public function addBackorderFilter()
    {
        $this->getSelect()->where("wi_quantity_to_ship > wi_physical_quantity");

        return $this;
    }

    public function addLowStockFilter($defaultWarningStockLevel)
    {
        $this->getSelect()->where("wi_available_quantity < if(wi_use_config_warning_stock_level = 1, ".$defaultWarningStockLevel.", wi_warning_stock_level)");

        return $this;
    }

    public function getProductIds()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(['wi_product_id']);
        $productIds = $this->getConnection()->fetchCol($this->getSelect());
        return $productIds;
    }

}
