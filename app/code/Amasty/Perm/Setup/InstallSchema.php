<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

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

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_perm_role')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'role_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Role ID'
        )->addForeignKey(
            $installer->getFkName('amasty_perm_role', 'role_id', 'authorization_role', 'role_id'),
            'role_id',
            $installer->getTable('authorization_role'),
            'role_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Amasty Perm Role'
        );

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_perm_dealer')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'role_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => null],
            'Role ID'
        )->addColumn(
            'user_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'User ID'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Description'
        )->addColumn(
            'emails',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Emails'
        )->addColumn(
            'customer_group_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Groups Ids'
        )->addForeignKey(
            $installer->getFkName('amasty_perm_dealer', 'role_id', 'amasty_perm_role', 'entity_id'),
            'role_id',
            $installer->getTable('amasty_perm_role'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->addForeignKey(
            $installer->getFkName('amasty_perm_dealer', 'user_id', 'admin_user', 'user_id'),
            'user_id',
            $installer->getTable('admin_user'),
            'user_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable('amasty_perm_dealer'),
                ['user_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['user_id'],
            [
                'type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ]
        )->setComment(
            'Amasty Perm Dealer'
        );

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_perm_dealer_customer')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'dealer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Dealer User ID'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Customer ID'
        )->addForeignKey(
            $installer->getFkName('amasty_perm_dealer_customer', 'dealer_id', 'amasty_perm_dealer', 'entity_id'),
            'dealer_id',
            $installer->getTable('amasty_perm_dealer'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('amasty_perm_dealer_customer', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable('amasty_perm_dealer_customer'),
                ['dealer_id', 'customer_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['dealer_id', 'customer_id'],
            [
                'type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ]
        )->setComment(
            'Amasty Perm Dealer Customer'
        );

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_perm_dealer_order')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'dealer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => null],
            'Dealer User ID'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Order Id'
        )->addColumn(
            'contactname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Contact Name'
        )->addForeignKey(
            $installer->getFkName('amasty_perm_dealer_order', 'dealer_id', 'amasty_perm_dealer', 'entity_id'),
            'dealer_id',
            $installer->getTable('amasty_perm_dealer'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->addForeignKey(
            $installer->getFkName('amasty_perm_dealer_order', 'order_id', 'sales_order', 'entity_id'),
            'order_id',
            $installer->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable('amasty_perm_dealer_order'),
                ['dealer_id', 'order_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['dealer_id', 'order_id'],
            [
                'type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ]
        )->setComment(
            'Amasty Perm Dealer Order'
        );

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_perm_dealer_order_assign_history')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Parent Id'
        )->addColumn(
            'is_dealer_notified',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Is Dealer Notified'
        )->addColumn(
            'comment',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Comment'
        )->addColumn(
            'author_dealer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => null],
            'Author Dealer ID'
        )->addColumn(
            'author_dealer_contactname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Author Dealer Contact Name'
        )->addColumn(
            'from_dealer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => null],
            'From Dealer ID'
        )->addColumn(
            'from_dealer_contactname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'From Dealer Contact Name'
        )->addColumn(
            'to_dealer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => null],
            'To Dealer ID'
        )->addColumn(
            'to_dealer_contactname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'To Dealer Contact Name'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addIndex(
            $installer->getIdxName('amasty_perm_dealer_order', ['parent_id']),
            ['parent_id']
        )->addIndex(
            $installer->getIdxName('amasty_perm_dealer_order', ['created_at']),
            ['created_at']
        )->addForeignKey(
            $installer->getFkName('amasty_perm_dealer_order_assign_history', 'parent_id', 'sales_order', 'entity_id'),
            'parent_id',
            $installer->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Amasty Perm Dealer Order Assign History'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}