<?php

namespace BoostMyShop\OrderPreparation\Setup;

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
         * Create table 'Orders in preparation progress'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bms_orderpreparation_inprogress'))
            ->addColumn('ip_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'In progress id')
            ->addColumn('ip_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Order id')
            ->addColumn('ip_user_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'User id')
            ->addColumn('ip_store_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Store id')
            ->addColumn('ip_status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [], 'Status')
            ->setComment('Orders in preparation progress');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'Orders in preparation progress items'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bms_orderpreparation_inprogress_item'))
            ->addColumn('ipi_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'In progress id')
            ->addColumn('ipi_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Order id')
            ->addColumn('ipi_order_item_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Order item id')
            ->addColumn('ipi_qty', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Qty')
            ->setComment('Orders in preparation progress items');
        $installer->getConnection()->createTable($table);


        $installer->endSetup();

    }
}
