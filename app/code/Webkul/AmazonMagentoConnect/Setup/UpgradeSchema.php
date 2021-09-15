<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'feedsubmission_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'product feedsubmission id of exported product of amazon'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'qty_feedsubmission_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'quantity feedsubmission id of exported product of amazon'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'price_feedsubmission_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'price feedsubmission id of exported product of amazon'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'img_feedsubmission_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'image feedsubmission id of exported product of amazon'
            ]
        );
        // add column for fulfillment channel amazon
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'fulfillment_channel',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'Fulfillment channel of amazon product'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_maped_order'),
            'fulfillment_channel',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'Fulfillment channel of amazon product'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_maped_order'),
            'error_msg',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '1M',
                'nullable' => false,
                'comment' => 'error message regarding amazon fulfillment order from magento '
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'product_sku',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'amazon product sku'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'export_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'export status'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'pro_status_at_amz',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'exported product status'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'error_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'amazon product status'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_maped_order'),
            'purchase_date',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'length' => null,
                'nullable' => false,
                'comment' => 'purchase date'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_tempdata'),
            'purchase_date',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'length' => null,
                'nullable' => false,
                'comment' => 'purchase date'
            ]
        );

        // create extra fields in seller account table
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'default_cate',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'default category for product assignment'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'default_store_view',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'default store view for product and order assignment'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'product_create',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'product create with or without variation'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'default_website',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'default website for product assignment'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'order_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'order status of amazon order'
            ]
        );
        // for product api
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'associate_tag',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'order status of amazon order'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'pro_api_secret_key',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'secret key of product api'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'pro_api_access_key_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'access key of product api'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_tempdata'),
            'product_sku',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'amazon seller sku'
            ]
        );

        // extra fields in wk_amazon_accounts tables
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'revise_item',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'length' => 2,
                'nullable' => false,
                'comment' => 'status of revise item'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'del_from_catalog',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'length' => 2,
                'nullable' => false,
                'comment' => 'status of delete product from catalog'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'all_images',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'length' => 2,
                'nullable' => false,
                'comment' => 'status of all images of amazon product'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'default_qty',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => 11,
                'nullable' => false,
                'comment' => 'default qty'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'default_weight',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => 11,
                'nullable' => false,
                'comment' => 'default weight'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'shipped_order',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 225,
                'nullable' => false,
                'comment' => 'shipped order status'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'unshipped_order',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 225,
                'nullable' => false,
                'comment' => 'unshipped order status'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'partiallyshipped_order',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 225,
                'nullable' => false,
                'comment' => 'partially shipped order status'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'price_rule',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 225,
                'nullable' => false,
                'comment' => 'price rule status'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'export_image',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'length' => 2,
                'nullable' => false,
                'comment' => 'main image will exported with product'
            ]
        );

        /*
        * Create table 'wk_amazon_pricerule
        */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_amazon_pricerule'))
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
                'price_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'product price from'
            )->addColumn(
                'price_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'product price to'
            )->addColumn(
                'sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'product sku'
            )->addColumn(
                'operation',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'product price operation'
            )->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Price'
            )->addColumn(
                'operation_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'product operation type ex. fixed/percent'
            )->addColumn(
                'amz_account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [],
                'amazon account id'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [],
                'status of rule'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'rule created time'
            )->addIndex(
                $setup->getIdxName('wk_amazon_pricerule', ['entity_id']),
                ['entity_id']
            )->setComment('Amazon Product Price Rule');

        $installer->getConnection()->createTable($table);

        /**
         * create table wk_amazon_attribute_map
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_amazon_attribute_map'))
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
                'mage_attr',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'magento product attribute code'
            )->addColumn(
                'amz_attr',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'amazon product attribute code'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [],
                'status of mapped record'
            )->addIndex(
                $setup->getIdxName('wk_amazon_attribute_map', ['entity_id']),
                ['entity_id']
            )->setComment('Amazon Product attribute map table');

        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
