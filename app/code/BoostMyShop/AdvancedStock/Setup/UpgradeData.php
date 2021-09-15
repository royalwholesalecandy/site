<?php

namespace BoostMyShop\AdvancedStock\Setup;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;


class UpgradeData implements UpgradeDataInterface
{

    protected $_websiteCollectionFactory;
    protected $_stockCollectionFactory;
    protected $_stockFactory;

    public function __construct(
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\CollectionFactory $stockCollectionFactory,
        \Magento\CatalogInventory\Model\StockFactory $stockFactory
    ) {
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_stockCollectionFactory = $stockCollectionFactory;
        $this->_stockFactory = $stockCollectionFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        //insert cataloginventory_stock for websites
        if (version_compare($context->getVersion(), '0.0.13') < 0)
        {
            $select = $setup->getConnection()->select()->from($setup->getTable('cataloginventory_stock'), ['website_id']);
            $existingWebsiteIds = $setup->getConnection()->fetchCol($select);

            $select = $setup->getConnection()->select()->from($setup->getTable('store_website'), ['website_id']);
            $allWebsiteIds = $setup->getConnection()->fetchCol($select);

            $missingWebsiteIds = array_diff($allWebsiteIds, $existingWebsiteIds);

            foreach($missingWebsiteIds as $websiteId)
            {
                $sql = 'insert into '.$setup->getTable('cataloginventory_stock').' (website_id, stock_name) values ('.$websiteId.', "For website #'.$websiteId.'")';
                $setup->getConnection()->query($sql);
            }

        }

        //insert cataloginventory_stock_items for websites
        if (version_compare($context->getVersion(), '0.0.14') < 0)
        {
            $sql = 'insert ignore
                    into '.$setup->getTable('cataloginventory_stock_item').'
                    (product_id, stock_id, qty, is_in_stock, website_id, max_sale_qty, notify_stock_qty, manage_stock, stock_status_changed_auto, qty_increments)
                    select
                        product_id, cs.stock_id, qty, is_in_stock, cs.website_id, max_sale_qty, notify_stock_qty, manage_stock, stock_status_changed_auto, qty_increments
                    from
                        '.$setup->getTable('cataloginventory_stock_item').' csi
                        join '.$setup->getTable('cataloginventory_stock').' cs
                    where
                        csi.stock_id = 1
                        and cs.stock_id > 1
                    ';
            $setup->getConnection()->query($sql);
        }

        $setup->endSetup();
    }

}
