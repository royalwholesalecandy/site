<?php

namespace BoostMyShop\AdvancedStock\Plugin\CatalogInventory\Ui\DataProvider\Product;

class AddQuantityFieldToCollection
{

    public function aroundAddField(\Magento\CatalogInventory\Ui\DataProvider\Product\AddQuantityFieldToCollection $subject, $proceed, $collection, $field, $alias)
    {
        $collection->joinField(
            'qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.website_id=0',
            'left'
        );

        return $this;
    }

}