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
 * @package   mirasvit/module-reports
 * @version   1.3.31
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Reports\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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

        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_reports_postcode')
        )->addColumn(
            'postcode_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Postcode Id'
        )->addColumn(
            'country_id',
            Table::TYPE_TEXT,
            2,
            ['unsigned' => false, 'nullable' => false],
            'Country Id'
        )->addColumn(
            'postcode',
            Table::TYPE_TEXT,
            20,
            ['unsigned' => false, 'nullable' => false],
            'Postcode'
        )->addColumn(
            'place',
            Table::TYPE_TEXT,
            180,
            ['unsigned' => false, 'nullable' => true],
            'Place'
        )->addColumn(
            'state',
            Table::TYPE_TEXT,
            100,
            ['unsigned' => false, 'nullable' => true],
            'State'
        )->addColumn(
            'province',
            Table::TYPE_TEXT,
            100,
            ['unsigned' => false, 'nullable' => true],
            'Province'
        )->addColumn(
            'community',
            Table::TYPE_TEXT,
            100,
            ['unsigned' => false, 'nullable' => true],
            'Community'
        )->addColumn(
            'lat',
            Table::TYPE_DECIMAL,
            '12,4',
            ['unsigned' => false, 'nullable' => false],
            'Lat'
        )->addColumn(
            'lng',
            Table::TYPE_DECIMAL,
            '12,4',
            ['unsigned' => false, 'nullable' => false],
            'Lng'
        )->addColumn(
            'updated',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Updated'
        )->addColumn(
            'original',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Original'
        )->addIndex(
            $installer->getIdxName('mst_reports_postcode', ['postcode']),
            ['postcode']
        )->addIndex(
            $installer->getIdxName('mst_reports_postcode', ['country_id']),
            ['country_id']
        );
        $installer->getConnection()->createTable($table);
    }
}
