<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\StockWebsite;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magento\CatalogInventory\Model\Stock\Item', 'Magento\CatalogInventory\Model\ResourceModel\Stock\Item');
    }

    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        $this->join(
            ['sw' => $this->getTable('store_website')],
            'main_table.website_id = sw.website_id',
            ['*']
        );

        $this->getSelect()->joinLeft(
            ['css' => $this->getTable('cataloginventory_stock_status')],
            'css.website_id = main_table.website_id and css.stock_id = main_table.stock_id and css.product_id = main_table.product_id',
            ['index_qty' => 'css.qty' , 'index_stock_status' => 'css.stock_status', 'index_stock_id'=> 'css.stock_id']
        );

        return $this;
    }

    public function addProductFilter($productId)
    {
        $this->addFieldToFilter('main_table.product_id', $productId);
        return $this;
    }

}
