<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\AmazonMagentoConnect\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

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
        $installer->startSetup();

        /*
         * Create table 'wk_amazon_accounts'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('wk_amazon_accounts'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false,'primary' => true],
                'Entity Id'
            )->addColumn(
                'attribute_set',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'attribute set id'
            )->addColumn(
                'store_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Amazon store name'
            )->addColumn(
                'country',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Magento Product Id'
            )->addColumn(
                'seller_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Amazon Seller Id'
            )->addColumn(
                'marketplace_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Amazon Marketplace Id'
            )->addColumn(
                'access_key_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'AWS Access Key ID'
            )->addColumn(
                'secret_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Secret Key'
            )->addColumn(
                'currency_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'corrency code'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Status'
            )->addColumn(
                'currency_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Currency code'
            )->addColumn(
                'currency_rate',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Currency rate according to magento base currency'
            )->addColumn(
                'listing_report_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Generated seller listing report id'
            )->addColumn(
                'inventory_report_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Generated seller inventory report id'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Amazon account add time'
            )->addIndex(
                $installer->getIdxName('wk_amazon_accounts', ['entity_id']),
                ['entity_id']
            )->setComment('Amazon Accounts');

        $installer->getConnection()->createTable($table);

        /*
         * Create table 'wk_amazon_mapped_product'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('wk_amazon_mapped_product'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'magento_pro_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Magento Product Id'
            )->addColumn(
                'amazon_pro_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Amazon Product Id'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Product Name'
            )->addColumn(
                'product_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Product Type'
            )->addColumn(
                'mage_cat_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Magento Category Id'
            )->addColumn(
                'mage_amz_account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Amazon account id magento store'
            )->addColumn(
                'change_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Change Status'
            )->addColumn(
                'amz_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Amazon product id'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Product sync Time'
            )->addIndex(
                $installer->getIdxName('wk_amazon_mapped_product', ['entity_id']),
                ['entity_id']
            )->setComment('Amazon Mapped Product');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_amazon_maped_order'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'amazon_order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Amazon Order Id'
            )->addColumn(
                'mage_order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Magento Order Id'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Order Status'
            )->addColumn(
                'mage_amz_account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Amazon account id magento store'
            )->addColumn(
                'purchase_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Order Place date at amazon'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Order Sync Time'
            )->addIndex(
                $installer->getIdxName('wk_amazon_maped_order', ['entity_id']),
                ['entity_id']
            )->setComment('Amazon Synchronize Order Table');

        $installer->getConnection()->createTable($table);

        /*
         * Create table 'wk_amazon_tempdata'
         */

        $table = $installer->getConnection()->newTable($installer->getTable('wk_amazon_tempdata'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'Entity Id'
            )->addColumn(
                'item_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Idenityfy that order or product'
            )->addColumn(
                'item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Amazon Item Id'
            )->addColumn(
                'item_data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3056,
                [],
                'Amazon item data in json format'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Import Time'
            )->addColumn(
                'mage_amz_account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Amazon account id magento store'
            )->addColumn(
                'amz_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Amazon product id'
            )->addIndex(
                $installer->getIdxName('wk_amazon_tempdata', ['entity_id']),
                ['entity_id']
            )->setComment('Amazon imported products/order temp table');

        $installer->getConnection()->createTable($table);
        /****/
        $installer->endSetup();
        $this->addForeignKeys($setup);
    }

    /**
     * inject foreign keys to table
     *
     * @param object $setup
     * @return void
     */
    public function addForeignKeys($setup)
    {
        /**
         * Add foreign keys for table wk_amazon_mapped_product
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'wk_amazon_mapped_product',
                'magento_pro_id',
                'catalog_product_entity',
                'entity_id'
            ),
            $setup->getTable('wk_amazon_mapped_product'),
            'magento_pro_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        /**
         * Add foreign keys for table wk_amazon_maped_order
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'wk_amazon_maped_order',
                'mage_amz_account_id',
                'wk_amazon_accounts',
                'entity_id'
            ),
            $setup->getTable('wk_amazon_maped_order'),
            'mage_amz_account_id',
            $setup->getTable('wk_amazon_accounts'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }
}
