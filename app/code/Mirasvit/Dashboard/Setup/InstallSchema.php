<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-dashboard
 * @version   1.2.35
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\Dashboard\Api\Data\BoardInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->getConnection()->dropTable($installer->getTable(BoardInterface::TABLE_NAME));

        $table = $installer->getConnection()->newTable(
            $installer->getTable(BoardInterface::TABLE_NAME)
        )->addColumn(
            BoardInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Board Id'
        )->addColumn(
            BoardInterface::TITLE,
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Title'
        )->addColumn(
            BoardInterface::IS_DEFAULT,
            Table::TYPE_INTEGER,
            1,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Is Default'
        )->addColumn(
            BoardInterface::TYPE,
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Type'
        )->addColumn(
            BoardInterface::USER_ID,
            Table::TYPE_INTEGER,
            11,
            ['unsigned' => false, 'nullable' => true],
            'User ID'
        )->addColumn(
            'widgets_serialized',
            Table::TYPE_TEXT,
            '64k',
            ['unsigned' => false, 'nullable' => false],
            'Widgets Serialized'
        )->addColumn(
            BoardInterface::CREATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            BoardInterface::UPDATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        );

        $installer->getConnection()->createTable($table);
    }
}
