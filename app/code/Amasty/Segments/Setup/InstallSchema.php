<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_segments_segment'))
            ->addColumn(
                'segment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'SegmentNew Id'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Name'
            )->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '1M',
                [],
                'Description'
            )->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 1],
                'Is active'
            )->addColumn(
                'conditions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '1M',
                [],
                'Conditions Serialized'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Updated At'
            )->setComment(
                'Amasty Segments Grid Table'
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_segments_website')
        )->addColumn(
            'segment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Segment Id'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Website Id'
        )->addIndex(
            $installer->getIdxName('amasty_segments_website', ['website_id']),
            ['website_id']
        )->addForeignKey(
            $installer->getFkName(
                'amasty_segments_website',
                'segment_id',
                'amasty_segments_segment',
                'segment_id'
            ),
            'segment_id',
            $installer->getTable('amasty_segments_segment'),
            'segment_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('amasty_segments_website', 'website_id', 'store_website', 'website_id'),
            'website_id',
            $installer->getTable('store_website'),
            'website_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Amasty Segments Website'
        );
        $installer->getConnection()->createTable($table);

        $tableName = 'amasty_segments_event';

        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'segment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Segment Id'
        )->addColumn(
            'event_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['primary' => true, 'nullable' => false, 'default' => ''],
            'Event Name'
        )->addForeignKey(
            $installer->getFkName(
                'amasty_segments_event',
                'segment_id',
                'amasty_segments_segment',
                'segment_id'
            ),
            'segment_id',
            $installer->getTable('amasty_segments_segment'),
            'segment_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Amasty Segments Event'
        );

        $installer->getConnection()->createTable($table);

        $tableName = 'amasty_segments_index';
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'index_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Index Id'
        )->addColumn(
            'segment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Segment Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Quote Id'
        )->addIndex(
            $installer->getIdxName($tableName, ['segment_id']),
            ['segment_id']
        )->addIndex(
            $installer->getIdxName($tableName, ['customer_id']),
            ['customer_id']
        )->addIndex(
            $installer->getIdxName($tableName, ['quote_id']),
            ['quote_id']
        )->addForeignKey(
            $installer->getFkName(
                'amasty_segments_index',
                'segment_id',
                'amasty_segments_segment',
                'segment_id'
            ),
            'segment_id',
            $installer->getTable('amasty_segments_segment'),
            'segment_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('amasty_segments_index', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('amasty_segments_index', 'quote_id', 'quote', 'entity_id'),
            'quote_id',
            $installer->getTable('quote'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Amasty Segments Index'
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
