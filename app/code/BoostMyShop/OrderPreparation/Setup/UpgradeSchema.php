<?php

namespace BoostMyShop\OrderPreparation\Setup;

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

        //0.0.3
        if (version_compare($context->getVersion(), '0.0.3', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_invoice_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Invoice ID'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_shipment_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Shipment ID'
                ]
            );

        }

        //0.0.4
        if (version_compare($context->getVersion(), '0.0.4', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_weights',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'size'  => 255,
                    'nullable' => true,
                    'comment' => 'Parcel weights'
                ]
            );
        }


        //0.0.5
        if (version_compare($context->getVersion(), '0.0.5', '<')) {

            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_orderpreparation_carrier_template'))
                ->addColumn('ct_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'id')
                ->addColumn('ct_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Name')
                ->addColumn('ct_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
                ->addColumn('ct_shipping_methods', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 500, [], 'Associated shipping methods')
                ->addColumn('ct_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [], 'Template type')
                ->addColumn('ct_disabled', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [], 'Disabled ?')
                ->addColumn('ct_export_file_mime', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Mime type')
                ->addColumn('ct_export_file_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Export file name')
                ->addColumn('ct_export_file_header', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2000, [], 'Export file header')
                ->addColumn('ct_export_file_order_header', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2000, [], 'Export file order header')
                ->addColumn('ct_export_file_order_products', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2000, [], 'Export file order products')
                ->addColumn('ct_export_file_order_footer', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2000, [], 'Export file order footer')
                ->addColumn('ct_import_file_separator', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 5, [], 'Import file separator')
                ->addColumn('ct_import_file_skip_first_line', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 5, [], 'Import file skip first line')
                ->addColumn('ct_import_file_shipment_reference_index', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 5, [], 'Import file shipment reference index')
                ->addColumn('ct_import_file_tracking_index', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 5, [], 'Import file tracking index')
                ->setComment('Carrier templates');
            $setup->getConnection()->createTable($table);
        }

        //0.0.8
        if (version_compare($context->getVersion(), '0.0.8', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_warehouse_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Warehouse ID'
                ]
            );
        }

        //0.0.9
        if (version_compare($context->getVersion(), '0.0.9', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress_item'),
                'ipi_parent_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'In progress ID'
                ]
            );
        }

        //0.0.10
        if (version_compare($context->getVersion(), '0.0.10', '<')) {

            $setup->getConnection()->truncateTable($setup->getTable('bms_orderpreparation_inprogress'));
            $setup->getConnection()->truncateTable($setup->getTable('bms_orderpreparation_inprogress_item'));
        }

        //0.0.11
        if (version_compare($context->getVersion(), '0.0.11', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_export_file_footer',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'size'  => 2000,
                    'nullable' => true,
                    'comment' => 'Export file footer',
                ]
            );
        }

        $setup->endSetup();
    }

}
