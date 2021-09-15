<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Stock\Item;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magento\CatalogInventory\Model\Stock\Item', 'Magento\CatalogInventory\Model\ResourceModel\Stock\Item');
    }

    public function addProductFilter($productId)
    {
        $this->getSelect()->where('product_id = '.$productId);
        return $this;
    }

    public function joinWebsite()
    {
        $this->getSelect()->join(
            ['tbl_website' => $this->getTable('store_website')],
            'main_table.website_id = tbl_website.website_id',
            ['code', 'name']
        );

        return $this;
    }

    public function addSimpleProductFilter()
    {
        $this->getSelect()->join(
            ['tbl_product' => $this->getTable('catalog_product_entity')],
            'main_table.product_id = tbl_product.entity_id and tbl_product.type_id = "simple"',
            []
        );

        return $this;
    }

    public function addNoBackorderFilter($defaultBackorderConfig)
    {
        $this->getSelect()->where('((use_config_backorders = 0 and backorders = 0) OR (use_config_backorders = 1 and '.$defaultBackorderConfig.' = 0))');
        return $this;
    }

}