<?php

namespace BoostMyShop\Organizer\Setup;

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
         * Create table 'bms_organizer'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bms_organizer'))
            ->addColumn('o_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Organizer id')
            ->addColumn('o_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
            ->addColumn('o_updated', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Updated at')
            ->addColumn('o_category', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Category')
            ->addColumn('o_priority', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Priority')
            ->addColumn('o_due_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Due date')
            ->addColumn('o_author_user_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Author user id')
            ->addColumn('o_assign_to_user_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Assign to user id')
            ->addColumn('o_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 400, [], 'Title')
            ->addColumn('o_comments', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'Comments')
            ->addColumn('o_status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Status')
            ->addColumn('o_object_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [], 'Object type')
            ->addColumn('o_object_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Object id')
            ->addColumn('o_object_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 400, [], 'Description')
            ->addIndex(
                $installer->getIdxName('bms_organizer', 'o_author_user_id'),
                ['o_author_user_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            )
            ->addIndex(
                $installer->getIdxName('bms_organizer', 'o_assign_to_user_id'),
                ['o_assign_to_user_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            )
            ->addIndex(
                $installer->getIdxName('bms_organizer', 'o_status'),
                ['o_status'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            )
            ->addIndex(
                $installer->getIdxName('bms_organizer', 'o_object_type'),
                ['o_object_type'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            )
            ->addIndex(
                $installer->getIdxName('bms_organizer', 'o_object_id'),
                ['o_object_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            )
            ->setComment('Organizer');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
