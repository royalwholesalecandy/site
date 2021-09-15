<?php

namespace BoostMyShop\AdvancedStock\Setup;

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

        $table = $setup->getConnection()
            ->newTable($setup->getTable('bms_advancedstock_warehouse'))
            ->addColumn('w_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('w_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [ 'unsigned' => true, 'nullable' => false, ], 'Warehouse Name')
            ->addColumn('w_contact', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 250, [ 'unsigned' => true, 'nullable' => true, ], 'Main contact')
            ->addColumn('w_email', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 250, [ 'unsigned' => true, 'nullable' => true, ], 'Email')
            ->addColumn('w_is_active', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [ 'unsigned' => true, 'nullable' => true, 'default' => 1], 'Active ?')
            ->addColumn('w_display_on_front', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [ 'unsigned' => true, 'nullable' => true, 'default' => 1], 'Display on front ?')
            ->addColumn('w_notes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 400, [ 'unsigned' => true, 'nullable' => true, ], 'Notes')
            ->addColumn('w_company_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 250, [ 'unsigned' => true, 'nullable' => true, ], 'Company name')
            ->addColumn('w_street1', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 250, [ 'unsigned' => true, 'nullable' => true, ], 'Street 1')
            ->addColumn('w_street2', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 250, [ 'unsigned' => true, 'nullable' => true, ], 'Street 2')
            ->addColumn('w_postcode', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [ 'unsigned' => true, 'nullable' => true, ], 'Postcode')
            ->addColumn('w_city', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [ 'unsigned' => true, 'nullable' => true, ], 'City')
            ->addColumn('w_state', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [ 'unsigned' => true, 'nullable' => true, ], 'Region')
            ->addColumn('w_country', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 3, [ 'unsigned' => true, 'nullable' => true, ], 'Country')
            ->addColumn('w_telephone', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [ 'unsigned' => true, 'nullable' => true, ], 'Telephone')
            ->addColumn('w_fax', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [ 'unsigned' => true, 'nullable' => true, ], 'Fax')
            ->addColumn('w_open_hours', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 400, [ 'unsigned' => true, 'nullable' => true, ], 'Open hours')
            ->setComment('Warehouse');
        $setup->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
