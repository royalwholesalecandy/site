<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\CatalogInventory;


class Stock extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('cataloginventory_stock', 'stock_id');
    }

    public function listStocks()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('cataloginventory_stock'), ['*']);
        return $this->getConnection()->fetchAll($select);
    }

    public function getIdFromWebsiteId($websiteId)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('cataloginventory_stock'), ['stock_id'])
            ->where('website_id = ' .$websiteId);
        return $this->getConnection()->fetchOne($select);
    }

    public function createStock($websiteId)
    {
        $sql = 'insert into '.$this->getTable('cataloginventory_stock').' (website_id, stock_name) values ('.$websiteId.', "For website #'.$websiteId.'")';
        $this->getConnection()->query($sql);

        return $this;
    }

    /**
     * Create stock item record based on website 0
     *
     * @param $warehouseId
     * @param $websiteId
     * @param null $productId
     * @return $this
     *
     */
    public function createStockItemRecords($warehouseId, $websiteId, $productId = null)
    {
        if (($websiteId == 0) && ($productId > 0))
        {
            $this->createStockItemRecordsFromScratch($warehouseId, $websiteId, $productId);
            return $this;
        }

        $sql = 'insert ignore
                    into '.$this->getTable('cataloginventory_stock_item').'
                    (product_id, stock_id, qty, is_in_stock, website_id, max_sale_qty, notify_stock_qty, manage_stock, use_config_manage_stock, stock_status_changed_auto, qty_increments)
                    select
                        product_id, '.$warehouseId.', IFNULL(qty, 0), is_in_stock, '.$websiteId.', max_sale_qty, notify_stock_qty, manage_stock, use_config_manage_stock, stock_status_changed_auto, qty_increments
                    from
                        '.$this->getTable('cataloginventory_stock_item').' csi
                    where
                        csi.stock_id = 1
                    ';
        if ($productId)
            $sql .= ' and product_id = '.$productId;

        $this->getConnection()->query($sql);

        return $this;
    }

    protected function createStockItemRecordsFromScratch($warehouseId, $websiteId, $productId)
    {
        $sql = 'insert into '.$this->getTable('cataloginventory_stock_item').'
                    (product_id, stock_id, qty, is_in_stock, website_id, max_sale_qty, notify_stock_qty, manage_stock, stock_status_changed_auto, qty_increments)
                values
                    (
                        '.$productId.',
                        '.$warehouseId.',
                        0,
                        1,
                        '.$websiteId.',
                        1000,
                        1,
                        1,
                        1,
                        1
                    )
                    ';

        $this->getConnection()->query($sql);

        return $this;

    }

}
