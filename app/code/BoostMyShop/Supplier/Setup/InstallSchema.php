<?php

namespace BoostMyShop\Supplier\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'suppliers'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bms_supplier'))
            ->addColumn('sup_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Supplier id')
            ->addColumn('sup_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
            ->addColumn('sup_updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Updated at')
            ->addColumn('sup_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Name')
            ->addColumn('sup_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 30, [], 'Code')
            ->addColumn('sup_contact', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 200, [], 'Contact')
            ->addColumn('sup_email', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 200, [], 'Email')
            ->addColumn('sup_website', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 200, [], 'Website')
            ->addColumn('sup_locale', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 8, [], 'Locale')
            ->addColumn('sup_is_active', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [], 'Is active')
            ->addColumn('sup_notes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'Notes')
            ->addColumn('sup_street1', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Street 1')
            ->addColumn('sup_street2', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Street 2')
            ->addColumn('sup_postcode', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 30, [], 'Postcode')
            ->addColumn('sup_city', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'City')
            ->addColumn('sup_state', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'State or Region')
            ->addColumn('sup_country', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 3, [], 'Country')
            ->addColumn('sup_telephone', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Telephone')
            ->addColumn('sup_fax', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Fax')
            ->addColumn('sup_minimum_of_order', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '12,2', [], 'Minimum of order')
            ->addColumn('sup_carriage_free_amount', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '12,2', [], 'Carriage free amount')
            ->addColumn('sup_currency', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 4, [], 'Currency')
            ->addColumn('sup_tax_rate', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '4,2', [], 'Tax rate')
            ->addColumn('sup_shipping_delay', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Shipping delay')
            ->addColumn('sup_shipping_instructions', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'Shipping instructions')
            ->addColumn('sup_sales_contact', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [], 'sales contact')
            ->addColumn('sup_accounting_contact', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [], 'accounting contact')
            ->addColumn('sup_aftersale_contact', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [], 'aftersale contact')
            ->addColumn('sup_sales_email', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [], 'sales email')
            ->addColumn('sup_accounting_email', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [], 'accounting email')
            ->addColumn('sup_aftersale_email', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [], 'aftersale email')
            ->addColumn('sup_sales_phone', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [], 'sales phone')
            ->addColumn('sup_accounting_phone', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [], 'accounting phone')
            ->addColumn('sup_aftersale_phone', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [], 'aftersale phone')
            ->addColumn('sup_sales_notes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '400', [], 'sales notes')
            ->addColumn('sup_accounting_notes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '400', [], 'accounting notes')
            ->addColumn('sup_aftersale_notes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '400', [], 'aftersale notes')
            ->addColumn('sup_opened_order', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Opened orders count')
            ->setComment('Suppliers');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'suppliers_product'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bms_supplier_product'))
            ->addColumn('sp_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Supplier product id')
            ->addColumn('sp_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
            ->addColumn('sp_updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Updated at')
            ->addColumn('sp_product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Product id')
            ->addColumn('sp_sup_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Supplier id')
            ->addColumn('sp_sku', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 200, [], 'Supplier sku')
            ->addColumn('sp_price', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Price')
            ->addColumn('sp_base_price', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Base price')
            ->setComment('Supplier products');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'purchase_order'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bms_purchase_order'))
            ->addColumn('po_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Purchase order id')
            ->addColumn('po_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
            ->addColumn('po_updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Updated at')
            ->addColumn('po_eta', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Estimated time of arrival')
            ->addColumn('po_sup_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Supplier id')
            ->addColumn('po_manager', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Manager')
            ->addColumn('po_reference', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 30, ['unsigned' => true, 'nullable' => false], 'Reference')
            ->addColumn('po_supplier_reference', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 30, ['unsigned' => true, 'nullable' => false], 'Supplier Reference')
            ->addColumn('po_invoice_reference', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 30, ['unsigned' => true, 'nullable' => false], 'Invoice Reference')
            ->addColumn('po_invoice_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Invoice date')
            ->addColumn('po_payment_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Payment date')
            ->addColumn('po_status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 30, [], 'Status')
            ->addColumn('po_public_comments', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2000, [], 'Comments')
            ->addColumn('po_private_comments', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2000, [], 'Comments')
            ->addColumn('po_delivery_progress', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Delivery progress')
            ->addColumn('po_store_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Store id')
            ->addColumn('po_warehouse_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Warehouse id')
            ->addColumn('po_currency', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 5, [], 'Currency')
            ->addColumn('po_change_rate', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Change rate')
            ->addColumn('po_shipping_cost', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Shipping cost')
            ->addColumn('po_shipping_cost_base', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Shipping cost base')
            ->addColumn('po_additionnal_cost', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Additionnal cost')
            ->addColumn('po_additionnal_cost_base', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Additionnal cost base')
            ->addColumn('po_tax_rate', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '4,2', [], 'Tax rate')
            ->addColumn('po_tax', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Tax')
            ->addColumn('po_tax_base', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Tax base')
            ->addColumn('po_subtotal', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Subtotal')
            ->addColumn('po_subtotal_base', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Subtotal base')
            ->addColumn('po_grandtotal', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Grandtotal')
            ->addColumn('po_grandtotal_base', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Grandtotal base')
            ->setComment('Purchase order');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'purchase_order_product'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bms_purchase_order_product'))
            ->addColumn('pop_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Purchase order product id')
            ->addColumn('pop_po_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Purchase order id')
            ->addColumn('pop_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
            ->addColumn('pop_updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Updated at')
            ->addColumn('pop_product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Product id')
            ->addColumn('pop_sku', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 30, [], 'Sku')
            ->addColumn('pop_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Name')
            ->addColumn('pop_supplier_sku', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 30, [], 'Supplier sku')
            ->addColumn('pop_qty', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Qty ordered')
            ->addColumn('pop_qty_received', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Qty received')
            ->addColumn('pop_price', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Buying price')
            ->addColumn('pop_price_base', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Buying price base')
            ->addColumn('pop_tax_rate', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '6,2', [], 'Tax rate')
            ->addColumn('pop_tax', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Tax rate')
            ->addColumn('pop_tax_base', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Tax rate')
            ->addColumn('pop_subtotal', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Row Subtotal')
            ->addColumn('pop_subtotal_base', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Row Subtotal base')
            ->addColumn('pop_grandtotal', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Row Total')
            ->addColumn('pop_grandtotal_base', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,4', [], 'Row Total base')
            ->setComment('Purchase order product');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'purchase_order_reception'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bms_purchase_order_reception'))
            ->addColumn('por_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Purchase order reception id')
            ->addColumn('por_po_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Purchase order id')
            ->addColumn('por_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
            ->addColumn('por_updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Updated at')
            ->addColumn('por_product_count', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Products received count')
            ->addColumn('por_username', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'User name')
            ->setComment('Purchase order reception');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'purchase_order_reception_item'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bms_purchase_order_reception_item'))
            ->addColumn('pori_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Purchase order reception item id')
            ->addColumn('pori_por_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Reception id')
            ->addColumn('pori_product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Product id')
            ->addColumn('pori_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
            ->addColumn('pori_qty', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Products received count')
            ->addColumn('pori_condition', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [], 'Products condition')
            ->setComment('Purchase order reception item');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
