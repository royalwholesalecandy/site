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
 * @package   mirasvit/module-report-builder
 * @version   1.0.24
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportBuilder\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\ReportBuilder\Api\Data\ConfigInterface;
use Mirasvit\ReportBuilder\Api\Data\ReportInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable(ReportInterface::TABLE_NAME)
        )->addColumn(
            ReportInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Report ID'
        )->addColumn(
            ReportInterface::NAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Title'
        )->addColumn(
            ReportInterface::CONFIG,
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Config'
        )->addColumn(
            ReportInterface::USER_ID,
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'User Id'
        );
        $installer->getConnection()->createTable($table);

        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable(ConfigInterface::TABLE_NAME)
        )->addColumn(
            ConfigInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Report ID'
        )->addColumn(
            ConfigInterface::TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Title'
        )->addColumn(
            ConfigInterface::CONFIG,
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Config'
        )->addColumn(
            ConfigInterface::USER_ID,
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'User Id'
        );
        $installer->getConnection()->createTable($table);
    }
}
