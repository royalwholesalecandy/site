<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var HelperCalculate
     */
    protected $helperCalculate;

    /**
     * @var ResourceCustomerPrices
     */
    protected $customerPricesResourceModel;

    /**
     * @var string
     */
    protected $entityId;

    /**
     * UpgradeSchema constructor.
     *
     * @param ResourceCustomerPrices $customerPricesResourceModel
     * @param HelperCalculate $helperCalculate
     * @throws \Exception
     */
    public function __construct(
        ResourceCustomerPrices $customerPricesResourceModel,
        HelperCalculate $helperCalculate
    ) {
        $this->customerPricesResourceModel = $customerPricesResourceModel;
        $this->helperCalculate             = $helperCalculate;
        $this->entityId                    = $this->helperCalculate->getLinkField();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->addTableMageWorxСatalogProductEntityDecimal($setup);
        $this->addTableMageWorCatalogProductIndexPrice($setup);
        $this->addTableMageWorxCatalogProductIndexPriceFinalTmp($setup);
        $this->addTableMageWorxCatalogProductIndexPriceOptTmp($setup);
        $this->addTableMageWorxCatalogProductIndexPriceOptAgrTmp($setup);
        $this->addTableMageWorxCatalogRuleProductPrice($setup);

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->updateTableMageWorxCustomerPrices($setup);
            $this->migrationData();
        }

        $this->deleteColumnProductName($setup);
        $this->deleteColumnEmail($setup);
        $this->deleteColumnCreatedTime($setup);
        $this->deleteColumnUpdatedAt($setup);

        $setup->endSetup();
    }

    /**
     * update table mageworx_customerprices
     *
     * @param SchemaSetupInterface $setup
     */
    protected function updateTableMageWorxCustomerPrices(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('mageworx_customerprices'),
            'price_sign',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 2,
                'nullable' => true,
                'comment'  => 'Price Sign',
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('mageworx_customerprices'),
            'price_value',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 50,
                'nullable' => true,
                'comment'  => 'Price Value',
            ]

        );

        $setup->getConnection()->addColumn(
            $setup->getTable('mageworx_customerprices'),
            'special_price_sign',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 2,
                'nullable' => true,
                'comment'  => 'Special Price Sign',
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('mageworx_customerprices'),
            'special_price_value',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 50,
                'nullable' => true,
                'comment'  => 'Special Price Value',
            ]

        );

        $setup->getConnection()->dropColumn(
            $setup->getTable('mageworx_customerprices'),
            'qty'
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable('mageworx_customerprices'),
            'attribute_id',
            'customer_id',
            [
                'type'     => Table::TYPE_INTEGER,
                'length'   => 10,
                'nullable' => false,
                'unsigned' => true,
                'primary'  => true,
                'comment'  => 'Customer ID'
            ]
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'mageworx_customerprices',
                'customer_id',
                'customer_entity',
                'entity_id'
            ),
            $setup->getTable('mageworx_customerprices'),
            'customer_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function addTableMageWorxСatalogProductEntityDecimal(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
                       ->newTable($setup->getTable('mageworx_catalog_product_entity_decimal_customer_prices'))
                       ->addColumn(
                           'value_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                           null,
                           ['identity' => true, 'nullable' => false, 'primary' => true],
                           'Value ID'
                       )
                       ->addColumn(
                           'attribute_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                           null,
                           ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                           'Attribute ID'
                       )
                       ->addColumn(
                           'store_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                           null,
                           ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                           'Store ID'
                       )
                       ->addColumn(
                           $this->entityId,
                           \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                           null,
                           ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                           'Entity ID'
                       )
                       ->addColumn(
                           'value',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                           '12,4',
                           [],
                           'Value'
                       )
                       ->addColumn(
                           'customer_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                           10,
                           ['unsigned' => true, 'nullable' => false, 'primary' => true],
                           'Customer ID'
                       )
                       ->addIndex(
                           $setup->getIdxName(
                               'mageworx_catalog_product_entity_decimal_customer_prices',
                               [$this->entityId, 'attribute_id', 'store_id', 'customer_id'],
                               \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                           ),
                           [$this->entityId, 'attribute_id', 'store_id', 'customer_id'],
                           ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                       )
                       ->addIndex(
                           $setup->getIdxName('mageworx_catalog_product_entity_decimal_customer_prices', ['store_id']),
                           ['store_id']
                       )
                       ->addIndex(
                           $setup->getIdxName(
                               'mageworx_catalog_product_entity_decimal_customer_prices',
                               ['attribute_id']
                           ),
                           ['attribute_id']
                       )
                       ->addForeignKey(
                           $setup->getFkName(
                               'mageworx_catalog_product_entity_decimal_customer_prices',
                               'attribute_id',
                               'eav_attribute',
                               'attribute_id'
                           ),
                           'attribute_id',
                           $setup->getTable('eav_attribute'),
                           'attribute_id',
                           \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                       )
                       ->addForeignKey(
                           $setup->getFkName(
                               'mageworx_catalog_product_entity_decimal_customer_prices',
                               $this->entityId,
                               'catalog_product_entity',
                               $this->entityId
                           ),
                           $this->entityId,
                           $setup->getTable('catalog_product_entity'),
                           $this->entityId,
                           \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                       )
                       ->addForeignKey(
                           $setup->getFkName(
                               'mageworx_catalog_product_entity_decimal_customer_prices',
                               'store_id',
                               'store',
                               'store_id'
                           ),
                           'store_id',
                           $setup->getTable('store'),
                           'store_id',
                           \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                       )
                       ->setComment('MageWorx Catalog Product Decimal Attribute Backend Table');
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function addTableMageWorCatalogProductIndexPrice(SchemaSetupInterface $setup)
    {
        $tableMageWorCatalogProductIndexPrice =
            $setup->getConnection()
                  ->newTable(
                      $setup->getTable('mageworx_catalog_product_index_price')
                  )
                  ->addColumn(
                      'entity_id',
                      \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                      null,
                      ['unsigned' => true, 'nullable' => false, 'primary' => true],
                      'Entity ID'
                  )
                  ->addColumn(
                      'website_id',
                      \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                      null,
                      ['unsigned' => true, 'nullable' => false, 'primary' => true],
                      'Website  ID '
                  )
                  ->addColumn(
                      'tax_class_id',
                      \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                      null,
                      ['unsigned' => true, 'default' => '0'],
                      'Tax  Class  ID'
                  )
                  ->addColumn(
                      'price',
                      \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                      '12,4',
                      [],
                      'Price '
                  )
                  ->addColumn(
                      'final_price',
                      \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                      '12,4',
                      [],
                      'Final  Price '
                  )
                  ->addColumn(
                      'min_price',
                      \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                      '12,4',
                      [],
                      'Min  Price '
                  )
                  ->addColumn(
                      'max_price',
                      \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                      '12,4',
                      [],
                      'Max  Price '
                  )
                  ->addColumn(
                      'customer_id',
                      \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                      10,
                      ['unsigned' => true, 'nullable' => false, 'primary' => true],
                      'Customer  ID '
                  )
                  ->addIndex(
                      $setup->getIdxName(
                          'mageworx_catalog_product_index_price',
                          ['min_price']
                      ),
                      ['min_price']
                  )
                  ->addIndex(
                      $setup->getIdxName(
                          'mageworx_catalog_product_index_price',
                          ['website_id', 'min_price']
                      ),
                      ['website_id', 'min_price']
                  )
                  ->addForeignKey(
                      $setup->getFkName(
                          'mageworx_catalog_product_index_price',
                          'customer_id',
                          'customer_entity',
                          'entity_id'
                      ),
                      'customer_id',
                      $setup->getTable('customer_entity'),
                      'entity_id',
                      \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                  )
                  ->addForeignKey(
                      $setup->getFkName(
                          'mageworx_catalog_product_index_price',
                          'entity_id',
                          'catalog_product_entity',
                          'entity_id'
                      ),
                      'entity_id',
                      $setup->getTable('catalog_product_entity'),
                      'entity_id',
                      \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                  )
                  ->addForeignKey(
                      $setup->getFkName(
                          'mageworx_catalog_product_index_price',
                          'website_id',
                          'store_website',
                          'website_id'
                      ),
                      'website_id',
                      $setup->getTable('store_website'),
                      'website_id',
                      \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                  )
                  ->setComment(
                      'Mageworx Catalog Product Price Index Table'
                  );

        $setup->getConnection()->createTable($tableMageWorCatalogProductIndexPrice);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function addTableMageWorxCatalogProductIndexPriceFinalTmp(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
                       ->newTable(
                           $setup->getTable('mageworx_catalog_product_index_price_final_tmp')
                       )
                       ->addColumn(
                           'entity_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                           null,
                           ['unsigned' => true, 'nullable' => false, 'primary' => true],
                           'Entity ID'
                       )
                       ->addColumn(
                           'website_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                           null,
                           ['unsigned' => true, 'nullable' => false, 'primary' => true],
                           'Website  ID '
                       )
                       ->addColumn(
                           'tax_class_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                           null,
                           ['unsigned' => true, 'default' => '0'],
                           'Tax  Class  ID'
                       )
                       ->addColumn(
                           'orig_price',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                           '12,4',
                           [],
                           'Original  Price '
                       )
                       ->addColumn(
                           'price',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                           '12,4',
                           [],
                           ' Price '
                       )
                       ->addColumn(
                           'min_price',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                           '12,4',
                           [],
                           'Min  Price '
                       )
                       ->addColumn(
                           'max_price',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                           '12,4',
                           [],
                           'Max  Price'
                       )
                       ->addColumn(
                           'customer_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                           10,
                           ['unsigned' => true, 'nullable' => false, 'primary' => true],
                           'Customer ID'
                       )
                       ->setOption(
                           'type',
                           \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
                       )
                       ->setComment(
                           'MageWorx Catalog Product Price Indexer Final Temp Table'
                       );
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function addTableMageWorxCatalogProductIndexPriceOptAgrTmp(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
                       ->newTable(
                           $setup->getTable('mageworx_catalog_product_index_price_opt_agr_tmp')
                       )
                       ->addColumn(
                           'entity_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                           null,
                           ['unsigned' => true, 'nullable' => false, 'primary' => true],
                           'Entity ID'
                       )
                       ->addColumn(
                           'website_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                           null,
                           ['unsigned' => true, 'nullable' => false, 'primary' => true],
                           'Website  ID'
                       )
                       ->addColumn(
                           'option_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                           null,
                           ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                           'Option  ID'
                       )
                       ->addColumn(
                           'min_price',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                           '12,4',
                           [],
                           'Min  Price'
                       )
                       ->addColumn(
                           'max_price',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                           '12,4',
                           [],
                           'Max  Price'
                       )
                       ->addColumn(
                           'customer_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                           10,
                           ['unsigned' => true, 'nullable' => false, 'primary' => true],
                           'Customer  ID'
                       )
                       ->setOption(
                           'type',
                           \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
                       )
                       ->setComment(
                           'MageWorx Catalog Product Price Indexer Option Aggregate Temp Table'
                       );
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function addTableMageWorxCatalogProductIndexPriceOptTmp(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
                       ->newTable(
                           $setup->getTable('mageworx_catalog_product_index_price_opt_tmp')
                       )
                       ->addColumn(
                           'entity_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                           null,
                           ['unsigned' => true, 'nullable' => false, 'primary' => true],
                           'Entity ID'
                       )
                       ->addColumn(
                           'website_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                           null,
                           ['unsigned' => true, 'nullable' => false, 'primary' => true],
                           'Website ID'
                       )
                       ->addColumn(
                           'min_price',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                           '12,4',
                           [],
                           'Min Price'
                       )
                       ->addColumn(
                           'max_price',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                           '12,4',
                           [],
                           'Max Price'
                       )
                       ->addColumn(
                           'customer_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                           10,
                           ['unsigned' => true, 'nullable' => false, 'primary' => true],
                           'Customer ID'
                       )
                       ->setOption(
                           'type',
                           \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
                       )
                       ->setComment(
                           'MageWorx Catalog Product Price Indexer Option Temp Table'
                       );
        $setup->getConnection()->createTable($table);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function migrationData()
    {
        $customerPricesCollection = $this->customerPricesResourceModel->loadCustomerPricesCollection();

        foreach ($customerPricesCollection as $customerPrice) {

            $this->customerPricesResourceModel->deleteProductCustomerPrice(
                $customerPrice['product_id'],
                $customerPrice['customer_id']
            );

            $priceType         = $this->helperCalculate->getPriceType($customerPrice['price']);
            $specialPriceType  = $this->helperCalculate->getPriceType($customerPrice['special_price']);
            $priceSign         = $this->helperCalculate->getPriceSign($customerPrice['price']);
            $specialPriceSign  = $this->helperCalculate->getPriceSign($customerPrice['special_price']);
            $priceValue        = abs(floatval($customerPrice['price']));
            $specialPriceValue = abs(floatval($customerPrice['special_price']));

            $this->customerPricesResourceModel->saveProductCustomerPrice(
                $customerPrice['attribute_type'],
                $customerPrice['customer_id'],
                $customerPrice['product_id'],
                $customerPrice['price'],
                $priceType,
                $customerPrice['special_price'],
                $specialPriceType,
                $customerPrice['discount'],
                $customerPrice['discount_price_type'],
                $priceSign,
                $priceValue,
                $specialPriceSign,
                $specialPriceValue
            );
        }
    }

    /**
     * create table mageworx_catalogrule_product_price
     *
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function addTableMageWorxCatalogRuleProductPrice(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
                       ->newTable($setup->getTable('mageworx_catalogrule_product_price'))
                       ->addColumn(
                           'rule_product_price_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                           null,
                           ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                           'Rule  Product  PriceId '
                       )
                       ->addColumn(
                           'rule_date',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                           null,
                           ['nullable' => false],
                           'Rule  Date '
                       )
                       ->addColumn(
                           'customer_group_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                           null,
                           ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                           'Customer  Group  Id '
                       )
                       ->addColumn(
                           'product_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                           null,
                           ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                           'Product  Id '
                       )
                       ->addColumn(
                           'rule_price',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                           [12, 4],
                           ['nullable' => false, 'default' => '0.0000'],
                           'Rule  Price '
                       )
                       ->addColumn(
                           'website_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                           null,
                           ['unsigned' => true, 'nullable' => false],
                           'Website  Id '
                       )
                       ->addColumn(
                           'latest_start_date',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                           null,
                           [],
                           'Latest StartDate'
                       )
                       ->addColumn(
                           'earliest_end_date',
                           \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                           null,
                           [],
                           'Earliest  EndDate '
                       )
                       ->addColumn(
                           'customer_id',
                           \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                           10,
                           ['unsigned' => true, 'nullable' => false, 'primary' => true],
                           'Customer  ID '
                       )
                       ->addIndex(
                           $setup->getIdxName(
                               'mageworx_catalogrule_product_price',
                               ['rule_date', 'website_id', 'customer_group_id', 'product_id', 'customer_id'],
                               \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                           ),
                           ['rule_date', 'website_id', 'customer_group_id', 'product_id', 'customer_id'],
                           ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                       )
                       ->addIndex(
                           $setup->getIdxName('mageworx_catalogrule_product_price', ['customer_group_id']),
                           ['customer_group_id']
                       )
                       ->addIndex(
                           $setup->getIdxName('mageworx_catalogrule_product_price', ['website_id']),
                           ['website_id']
                       )
                       ->addIndex(
                           $setup->getIdxName('mageworx_catalogrule_product_price', ['product_id']),
                           ['product_id']
                       )
                       ->addForeignKey(
                           $setup->getFkName(
                               'mageworx_catalogrule_product_price',
                               'customer_id',
                               'customer_entity',
                               'entity_id'
                           ),
                           'customer_id',
                           $setup->getTable('customer_entity'),
                           'entity_id',
                           \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                       )
                       ->setComment('MageWorx CatalogRule Product Price');

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function deleteColumnProductName(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('mageworx_customerprices');
        $setup->getConnection()->dropColumn($tableName, 'product_name');
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function deleteColumnEmail(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('mageworx_customerprices');
        $setup->getConnection()->dropColumn($tableName, 'email');
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function deleteColumnCreatedTime(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('mageworx_customerprices');
        $setup->getConnection()->dropColumn($tableName, 'created_time');
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function deleteColumnUpdatedAt(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('mageworx_customerprices');
        $setup->getConnection()->dropColumn($tableName, 'updated_at');
    }


}