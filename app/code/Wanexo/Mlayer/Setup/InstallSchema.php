<?php
namespace Wanexo\Mlayer\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
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

        if (!$installer->tableExists('wanexo_mlayer_banner')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('wanexo_mlayer_banner'));
            $table->addColumn(
                    'banner_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Banner ID'
                )
                ->addColumn(
                    'title',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable'  => false,],
                    'Banner Title'
                )
				
				->addColumn(
                    'btntext',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable'  => false,],
                    'Button Text'
                )
				
                ->addColumn(
                    'web_url',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable'  => false,],
                    'Banner Url Key'
                )
                ->addColumn(
                    'banner_content',
                    Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Banner Content'
                )
                ->addColumn(
                    'content_position',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Banner Content Position'
                )
				
                ->addColumn(
                    'banner_image',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Banner Image'
                )
                ->addColumn(
                    'is_active',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable'  => false,
                        'default'   => '1',
                    ],
                    'Is Banner Active'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Update at'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Creation Time'
                )
                ->setComment('Mlayer Banners');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('wanexo_mlayer_banner'),
                $setup->getIdxName(
                    $installer->getTable('wanexo_mlayer_banner'),
                    ['title'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                [
                    'title'
                   
                ],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ); 
			
			$installer->run("

			INSERT INTO {$installer->getTable('wanexo_mlayer_banner')} (`banner_id`, `title`, `btntext`, `banner_content`, `content_position`, `banner_image`, `is_active`, `updated_at`, `created_at`, `web_url`) VALUES
(1, 'First Banner', 'Shop Now', '<p>This is also a great way to enable quick theme changing and modification. If you develop a number of themes for your company to use as templates on demand you can include all of them as bundles in the same LESS file and use use the one you need</p>', 1, '/b/n/bn1.jpg', 1, '2016-09-02 08:49:29', '2016-02-01 13:35:15', '#'),
(2, 'Second Banner',  'Buy Now', '<p>This is also a great way to enable quick theme changing and modification. If you develop a number of themes for your company to use as templates on demand you can include all of them as bundles in the same LESS file and use use the one you need</p>', 2, '/b/n/bn2.jpg', 1, '2016-09-02 08:37:32', '2016-02-01 13:35:42', '#'),
(3, 'Third Banner',  'Buy', '<p>This is also a great way to enable quick theme changing and modification. If you develop a number of themes for your company to use as templates on demand you can include all of them as bundles in the same LESS file and use use the one you need</p>', 1, '/b/n/bn3.jpg', 1, '2016-09-02 08:42:25', '2016-09-02 07:33:55', '#');

			");
        }

        //Create Banners to Store table
        if (!$installer->tableExists('wanexo_mlayer_banner_store')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('wanexo_mlayer_banner_store'));
            $table->addColumn(
                    'banner_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'   => true,
                    ],
                    'Banner ID'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned'  => true,
                        'nullable'  => false,
                        'primary'   => true,
                    ],
                    'Store ID'
                )
                ->setComment('Banner To Store Link Table');
            $installer->getConnection()->createTable($table);
			
			$installer->run("

			INSERT INTO {$installer->getTable('wanexo_mlayer_banner_store')} (`banner_id`, `store_id`) VALUES
(1, 0),
(2, 0);

			");
			
        }
		$installer->endSetup();
    }
}
