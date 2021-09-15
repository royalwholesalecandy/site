<?php

namespace BoostMyShop\AdvancedStock\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{


    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.2', '<')) {


            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_advancedstock_stock_movement'))
                ->addColumn('sm_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Supplier product id')
                ->addColumn('sm_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
                ->addColumn('sm_product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Product id')
                ->addColumn('sm_from_warehouse_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Source warehouse')
                ->addColumn('sm_to_warehouse_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Target warehouse')
                ->addColumn('sm_qty', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Quantity')
                ->addColumn('sm_category', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Category')
                ->addColumn('sm_comments', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100, [], 'Comments')
                ->addColumn('sm_user_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'User id')
                ->addColumn('sm_parent_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Parent id')
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_stock_movement', ['sm_product_id']),
                    ['sm_product_id']
                )
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_stock_movement', ['sm_from_warehouse_id']),
                    ['sm_from_warehouse_id']
                )
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_stock_movement', ['sm_to_warehouse_id']),
                    ['sm_to_warehouse_id']
                )
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_stock_movement', ['sm_parent_id']),
                    ['sm_parent_id']
                )
                ->setComment('Stock movements');
            $setup->getConnection()->createTable($table);

        }

        if (version_compare($context->getVersion(), '0.0.3', '<')) {

            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_advancedstock_warehouse_item'))
                ->addColumn('wi_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
                ->addColumn('wi_warehouse_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Warehouse id')
                ->addColumn('wi_product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Product id')
                ->addColumn('wi_physical_quantity', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['default' => '0'], 'Physical quantity')
                ->addColumn('wi_available_quantity', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['default' => '0'], 'Available quantity')
                ->addColumn('wi_reserved_quantity', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['default' => '0'], 'Reserved quantity')
                ->addColumn('wi_shelf_location', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [], 'Shelf location')
                ->addColumn('wi_quantity_to_ship', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['default' => '0'], 'Quantity to ship')
                ->addColumn('wi_warning_stock_level', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['default' => '0'], 'warning stock level')
                ->addColumn('wi_use_config_warning_stock_level', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['default' => '1'], 'Use config warning stock level')
                ->addColumn('wi_ideal_stock_level', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['default' => '0'], 'Ideal stock level')
                ->addColumn('wi_use_config_ideal_stock_level', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['default' => '1'], 'Use config ideal stock level')
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_warehouse_item', ['wi_warehouse_id']),
                    ['wi_warehouse_id']
                )
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_warehouse_item', ['wi_product_id']),
                    ['wi_product_id']
                )
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_warehouse_item', ['wi_product_id', 'wi_warehouse_id']),
                    ['wi_product_id', 'wi_warehouse_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('Warehouse items');
            $setup->getConnection()->createTable($table);

        }

        if (version_compare($context->getVersion(), '0.0.5', '<')) {

            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_advancedstock_extended_sales_flat_order_item'))
                ->addColumn('esfoi_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
                ->addColumn('esfoi_order_item_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Order item id')
                ->addColumn('esfoi_warehouse_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Preparation warehouse id')
                ->addColumn('esfoi_qty_reserved', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['default' => '0'], 'Qty reserved')
                ->addColumn('esfoi_qty_to_ship', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['default' => '0'], 'Qty to ship')
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_extended_sales_flat_order_item', ['esfoi_order_item_id']),
                    ['esfoi_order_item_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_extended_sales_flat_order_item', ['esfoi_warehouse_id']),
                    ['esfoi_warehouse_id']
                )
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_extended_sales_flat_order_item', ['esfoi_qty_to_ship']),
                    ['esfoi_qty_to_ship']
                )
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_extended_sales_flat_order_item', ['esfoi_qty_reserved']),
                    ['esfoi_qty_reserved']
                )
                ->setComment('Extended sales flat order item');
            $setup->getConnection()->createTable($table);

        }


        if (version_compare($context->getVersion(), '0.0.6', '<')) {

            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_advancedstock_routing_store'))
                ->addColumn('rs_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
                ->addColumn('rs_website_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Website id')
                ->addColumn('rs_group_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Group id')
                ->addColumn('rs_store_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Store id')
                ->addColumn('rs_use_default', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Use default')
                ->addColumn('rs_routing_mode', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Routing mode')
                ->setComment('Routing store');
            $setup->getConnection()->createTable($table);

        }

        if (version_compare($context->getVersion(), '0.0.7', '<')) {

            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_advancedstock_routing_store_warehouse'))
                ->addColumn('rsw_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
                ->addColumn('rsw_website_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Website id')
                ->addColumn('rsw_group_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Group id')
                ->addColumn('rsw_store_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Store id')
                ->addColumn('rsw_warehouse_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Warehouse id')
                ->addColumn('rsw_use_default', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Use default')
                ->addColumn('rsw_use_for_sales', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Use for sales')
                ->addColumn('rsw_use_for_shipments', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Use for shipments')
                ->addColumn('rsw_priority', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['default' => 1], 'Priority')
                ->setComment('Routing store warehouse');
            $setup->getConnection()->createTable($table);

        }


        if (version_compare($context->getVersion(), '0.0.8', '<')) {

            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_advancedstock_sales_history'))
                ->addColumn('sh_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
                ->addColumn('sh_warehouse_item_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Warehouse item id')
                ->addColumn('sh_range_1', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Sales for range 1')
                ->addColumn('sh_range_2', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Sales for range 2')
                ->addColumn('sh_range_3', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Sales for range 3')
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_sales_history', ['sh_warehouse_item_id']),
                    ['sh_warehouse_item_id']
                )
                ->setComment('Sales history per warehouse');
            $setup->getConnection()->createTable($table);

        }

        if (version_compare($context->getVersion(), '0.0.9', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_warehouse'),
                'w_is_primary',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Is primary',
                    'default' => 0
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.10', '<')) {

            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_advancedstock_transfer'))
                ->addColumn('st_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
                ->addColumn('st_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Date of creation')
                ->addColumn('st_reference', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Transfer reference')
                ->addColumn('st_from', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'From warehouse')
                ->addColumn('st_to', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'To warehouse')
                ->addColumn('st_status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [], 'Status')
                ->addColumn('st_notes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2000, [], 'Notes')
                ->addIndex($setup->getIdxName('bms_advancedstock_transfer', ['st_status']), ['st_status'])
                 ->setComment('Transfers');
            $setup->getConnection()->createTable($table);

            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_advancedstock_transfer_item'))
                ->addColumn('sti_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
                ->addColumn('st_transfer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Transfer')
                ->addColumn('st_product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Product ID')
                ->addColumn('st_qty', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Qty')
                ->addColumn('st_qty_transfered', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Qty transfered')
                ->addColumn('st_notes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 400, [], 'NOtes')
                ->addIndex($setup->getIdxName('bms_advancedstock_transfer_item', ['st_transfer_id']), ['st_transfer_id'])
                ->addIndex($setup->getIdxName('bms_advancedstock_transfer_item', ['st_product_id']), ['st_product_id'])
                ->setComment('Transfer items');
            $setup->getConnection()->createTable($table);

        }

        if(version_compare($context->getVersion(), '0.0.11', '<')){

            $setup->getConnection()->modifyColumn(
                $setup->getTable('bms_advancedstock_transfer'),
                'st_from',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'unsigned' => true, 'nullable' => false]
            );

            $setup->getConnection()->modifyColumn(
                $setup->getTable('bms_advancedstock_transfer'),
                'st_to',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'unsigned' => true, 'nullable' => false]
            );

            $setup->getConnection()->addForeignKey(
                $setup->getFkName('bms_advancedstock_transfer', 'st_to', $setup->getTable('bms_advancedstock_warehouse'), 'w_id'),
                $setup->getTable('bms_advancedstock_transfer'),
                'st_to',
                $setup->getTable('bms_advancedstock_warehouse'),
                'w_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
            );

            $setup->getConnection()->addForeignKey(
                $setup->getFkName('bms_advancedstock_transfer', 'st_from', $setup->getTable('bms_advancedstock_warehouse'), 'w_id'),
                $setup->getTable('bms_advancedstock_transfer'),
                'st_from',
                $setup->getTable('bms_advancedstock_warehouse'),
                'w_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
            );

            $setup->getConnection()->modifyColumn(
                $setup->getTable('bms_advancedstock_transfer_item'),
                'st_transfer_id',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'unsigned' => true, 'nullable' => false]
            );

            $setup->getConnection()->addForeignKey(
                $setup->getFkName('bms_advancedstock_transfer_item', 'st_transfer_id', $setup->getTable('bms_advancedstock_transfer'), 'st_id'),
                $setup->getTable('bms_advancedstock_transfer_item'),
                'st_transfer_id',
                $setup->getTable('bms_advancedstock_transfer'),
                'st_id'
            );

        }

        if (version_compare($context->getVersion(), '0.0.12', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_warehouse'),
                'w_use_in_supplyneeds',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Use for supply needs',
                    'default' => 1
                ]
            );

        }


        if (version_compare($context->getVersion(), '0.0.15', '<')) {


            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_advancedstock_stock_take'))
                ->addColumn('sta_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
                ->addColumn('sta_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [], 'Name')
                ->addColumn('sta_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
                ->addColumn('sta_warehouse_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Warehouse Id')
                ->addColumn('sta_status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [], 'Status')
                ->addColumn('sta_notes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 400, [], 'Notes')
                ->addColumn('sta_per_location', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [], 'Per location')
                ->addIndex($setup->getIdxName('bms_advancedstock_stock_take', ['sta_warehouse_id']), ['sta_warehouse_id'])
                ->addForeignKey(
                    $setup->getFkName('bms_advancedstock_stock_take', 'sta_warehouse_id', $setup->getTable('bms_advancedstock_warehouse'), 'w_id'),
                    'sta_warehouse_id',
                    $setup->getTable('bms_advancedstock_warehouse'),
                    'w_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
                )
                ->setComment('Stock Takes');
            $setup->getConnection()->createTable($table);

            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_advancedstock_stock_take_item'))
                ->addColumn('stai_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
                ->addColumn('stai_stock_take_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Stock take Id')
                ->addColumn('stai_sku', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 64, ['nullable' => false], 'Product SKU')
                ->addColumn('stai_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [], 'Product Name')
                ->addColumn('stai_expected_qty', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false] , 'Expected qty')
                ->addColumn('stai_scanned_qty', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Scanned qty')
                ->addColumn('stai_location', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [], 'Location')
                ->addIndex($setup->getIdxName('bms_advancedstock_stock_take_item', ['stai_stock_take_id']), ['stai_stock_take_id'])
                ->addIndex($setup->getIdxName('bms_advancedstock_stock_take_item', ['stai_sku']), ['stai_sku'])
                ->addForeignKey(
                    $setup->getFkName('bms_advancedstock_stock_take_item', 'stai_stock_take_id', $setup->getTable('bms_advancedstock_stock_take'), 'sta_id'),
                    'stai_stock_take_id',
                    $setup->getTable('bms_advancedstock_stock_take'),
                    'sta_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Stock take items');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_stock_take'),
                'sta_product_selection',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => null,
                    'nullable' => false,
                    'comment' => 'Product Selection'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_stock_take'),
                'sta_manager_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment' => 'Manager id'
                ]
            );

            $setup->getConnection()->addForeignKey(
                $setup->getFkName('bms_advancedstock_stock_take', 'sta_manager_id', $setup->getTable('admin_user'), 'user_id'),
                $setup->getTable('bms_advancedstock_stock_take'),
                'sta_manager_id',
                $setup->getTable('admin_user'),
                'user_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
            );


            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_stock_take_item'),
                'stai_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => null,
                    'nullable' => false,
                    'comment' => 'Stock take item status'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_stock_take'),
                'sta_progress',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    'length' => null,
                    'nullable' => false,
                    'comment' => 'Progress percent'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_stock_take_item'),
                'stai_manufacturer',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => null,
                    'nullable' => false,
                    'comment' => 'Manufacturer'
                ]
            );


            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_stock_take'),
                'sta_manufacturers',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Selected manufacturers'
                ]
            );


        }


        if (version_compare($context->getVersion(), '0.0.16', '<')) {

            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_advancedstock_stock_movement_logs'))
                ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Sm log id')
                ->addColumn('sm_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Sm id')
                ->addColumn('log', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 400, [ 'unsigned' => true, 'nullable' => true, ], 'Log')
                ->addIndex(
                    $setup->getIdxName('bms_advancedstock_stock_movement_logs', ['sm_id']),
                    ['sm_id']
                )
                ->setComment('Stock movements logs');
            $setup->getConnection()->createTable($table);

        }

        if (version_compare($context->getVersion(), '0.0.17', '<')) {

            $setup->getConnection()->dropForeignKey(
                $setup->getTable('bms_advancedstock_transfer'),
                $setup->getFkName('bms_advancedstock_transfer', 'st_to', $setup->getTable('bms_advancedstock_warehouse'), 'w_id')
            );

            $setup->getConnection()->dropForeignKey(
                $setup->getTable('bms_advancedstock_transfer'),
                $setup->getFkName('bms_advancedstock_transfer', 'st_from', $setup->getTable('bms_advancedstock_warehouse'), 'w_id')
            );
        }


        if (version_compare($context->getVersion(), '0.0.18', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_stock_take'),
                'sta_website',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Matching website #'
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.19', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_warehouse'),
                'w_website',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Website #'
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.20', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_transfer'),
                'st_website_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Website #'
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.21', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_stock_movement'),
                'sm_ui',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 20,
                    'nullable' => true,
                    'comment' => 'Stock movement unique ID'
                ]
            );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable('bms_advancedstock_stock_movement'),
                    $setup->getIdxName('bms_advancedstock_stock_movement', ['sm_ui']),
                    'sm_ui',
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                );

        }


        if (version_compare($context->getVersion(), '0.0.22', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_warehouse'),
                'w_fulfilment_method',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => 1,
                    'comment' => 'Fulfilment method'
                ]
            );
        }

        $setup->endSetup();
    }


}
