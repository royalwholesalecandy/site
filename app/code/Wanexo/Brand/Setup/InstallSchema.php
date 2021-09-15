<?php

namespace Wanexo\Brand\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;


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

        if (!$installer->tableExists('wanexo_brand')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('wanexo_brand'));
            $table->addColumn(
                    'brand_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Brand ID'
                )
                ->addColumn(
                    'brand_option_name',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Brand Option'
                )
                ->addColumn(
                    'brand_title',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Brand Name'
                )
                ->addColumn(
                    'brand_image',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Image Name'
                )
                ->addColumn(
                    'brand_thumbimage',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Thumb Image Name'
                )
                ->addColumn(
                    'content',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Brand Content'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_TEXT,
                    null,
                    ['default' => null]
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    []
                )
                ->addColumn(
                    'position',
                    Table::TYPE_INTEGER,
                    null,
                    []
                )
				->addColumn(
                    'option_id',
                    Table::TYPE_INTEGER,
                    null,
                    []
                )
                ->setComment('Testimonial Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
            
            $installer->getConnection()->addIndex(
                $installer->getTable('wanexo_brand'),
                $setup->getIdxName(
                    $installer->getTable('wanexo_brand'),
                    ['brand_title'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                [
                    'brand_title',
                ],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
			

        }
        $installer->endSetup();
    }
}
