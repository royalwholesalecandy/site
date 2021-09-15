<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;

class InstallSchema implements InstallSchemaInterface
{
    const TABLE_NAME = 'mageworx_customergroupprices';

    /**
     * @var HelperData
     */
    protected $helperData;

    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $customerGroupTable  = $setup->getConnection()->describeTable($setup->getTable('customer_group'));
        $customerGroupIdType = $customerGroupTable['customer_group_id']['DATA_TYPE'] == 'int'
            ? \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER : $customerGroupTable['customer_group_id']['DATA_TYPE'];
        $customerGroupIdSize = ($customerGroupIdType == \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER) ? 10 : 5;
        $id                  = $this->helperData->getLinkField();

        /**
         * Create table 'mageworx_customergroupprices'
         */
        $tableMageWorxCustomerGroupPrices = $installer->getConnection()->newTable(
            $installer->getTable('mageworx_customergroupprices')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'Entity Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Product Id'
        )->addColumn(
            'group_id',
            $customerGroupIdType,
            $customerGroupIdSize,
            ['unsigned' => true, 'nullable' => false],
            'Group Id'
        )->addColumn(
            'is_all_groups',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'All Groups'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Website Id'
        )->addColumn(
            'math_sign',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            2,
            [],
            'Math sign'
        )->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'Product Price'
        )->addColumn(
            'price_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Product Price Type(Fixed,Percent)'
        )->addColumn(
            'absolute_price_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Absolute Price Type(Price,Special Price)'
        )->addColumn(
            'assign_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Assign Price (if 0 - for group, 1 - for product).'
        )->addColumn(
            'is_manual',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '1'],
            'If 0 - price create automatically, 1 - create from interface.'
        )->addIndex(
            $installer->getIdxName(self::TABLE_NAME, ['product_id']),
            ['product_id']
        )->addIndex(
            $installer->getIdxName(self::TABLE_NAME, ['website_id']),
            ['website_id']
        )->addIndex(
            $installer->getIdxName(self::TABLE_NAME, ['is_all_groups']),
            ['is_all_groups']
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE_NAME,
                'group_id',
                'customer_group',
                'customer_group_id'
            ),
            'group_id',
            $installer->getTable('customer_group'),
            'customer_group_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        /**
         * Create table 'mageworx_catalog_product_entity_decimal_temp'
         */
        $tableCatalogProductEntityDecimalTemp = $installer->getConnection()->newTable(
            $installer->getTable('mageworx_catalog_product_entity_decimal_temp')
        )->addColumn(
            'value_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Value ID'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => '0'
            ],
            'Attribute ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => '0'
            ],
            'Store ID'
        )->addColumn(
            $id,
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'ID'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Value'
        )->addColumn(
            'customer_group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Customer Group Id'
        )->addIndex(
            $installer->getIdxName(
                'mageworx_catalog_product_entity_decimal_temp',
                ['store_id']
            ),
            ['store_id']
        )->addIndex(
            $installer->getIdxName(
                'mageworx_catalog_product_entity_decimal_temp',
                ['attribute_id']
            ),
            ['attribute_id']
        )->addIndex(
            $installer->getIdxName(
                'mageworx_catalog_product_entity_decimal_temp',
                ['value']
            ),
            ['value']
        )->addIndex(
            $installer->getIdxName('mageworx_catalog_product_entity_decimal_temp', [$id]),
            [$id]
        );

        $installer->getConnection()->createTable($tableMageWorxCustomerGroupPrices);
        $installer->getConnection()->createTable($tableCatalogProductEntityDecimalTemp);

        $installer->endSetup();
    }
}