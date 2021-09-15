<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Model\Indexer;

use MageWorx\CustomerPrices\Helper\Data as HelperData;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;

/**
 * Build select for rule relation with product.
 */
class RuleProductsSelectBuilder
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperCalculate
     */
    protected $helperCalculate;

    /**
     * @var ResourceCustomerPrices
     */
    protected $customerPricesResourceModel;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var String
     */
    protected $tableNameCustomerEntity;

    /**
     * RuleProductsSelectBuilder constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param HelperData $helperData
     * @param HelperCalculate $helperCalculate
     * @parma ResourceCustomerPrices $customerPricesResourceModel
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        HelperData $helperData,
        HelperCalculate $helperCalculate,
        ResourceCustomerPrices $customerPricesResourceModel
    ) {
        $this->eavConfig                   = $eavConfig;
        $this->storeManager                = $storeManager;
        $this->metadataPool                = $metadataPool;
        $this->resource                    = $resource;
        $this->objectManager               = $objectmanager;
        $this->helperData                  = $helperData;
        $this->helperCalculate             = $helperCalculate;
        $this->customerPricesResourceModel = $customerPricesResourceModel;
        $this->connection                  = $this->resource->getConnection();
        $this->tableNameCustomerEntity     = $this->resource->getTableName('customer_entity');
    }

    /**
     * @param int $websiteId
     * @param \Magento\Catalog\Model\Product|null $product
     * @param bool $useAdditionalTable
     * @return \Zend_Db_Statement_Interface
     * @throws \Magento\Framework\Exception\FileSystemException]
     */
    public function build(
        $websiteId,
        \Magento\Catalog\Model\Product $product = null,
        $useAdditionalTable = false
    ) {
        $indexTable              = $this->resource->getTableName('catalogrule_product');
        $entityId                = $this->helperCalculate->getLinkField();
        $priceAttributeId        = $this->customerPricesResourceModel->getPriceAttributeId();
        $specialPriceAttributeId = $this->customerPricesResourceModel->getSpecialPriceAttributeId();

        if ($useAdditionalTable &&
            version_compare(
                $this->helperCalculate->getModuleVersion(
                    'Magento_Catalog'
                ),
                '102.0.0',
                '>'
            )) {
            $activeTableSwitcher = $this->objectManager->get(
                'Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher'
            );
            $indexTable          = $this->resource->getTableName(
                $activeTableSwitcher->getAdditionalTableName('catalogrule_product')
            );
        }

        $select = $this->baseJoin($product, $indexTable);

        /* Join default price and websites prices to result */
        $this->joinDefaultPriceAndWebsitesPrices($select, $websiteId);

        /* join mageworx customer data */
        $this->joinMageworxDecimalPrice($select, $entityId, $priceAttributeId);
        $this->joinMageworxDecimalSpecialPrice($select, $entityId, $specialPriceAttributeId);

        return $this->connection->query($select);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $indexTable
     * @return mixed
     */
    protected function baseJoin($product, $indexTable)
    {
        $select = $this->connection->select()->from(
            ['rp' => $indexTable]
        )->order(
            ['rp.website_id', 'rp.customer_group_id', 'rp.product_id', 'rp.sort_order', 'rp.rule_id']
        );

        if ($product && $product->getEntityId()) {
            $select->where('rp.product_id=?', $product->getEntityId());
        }

        return $select;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param int $websiteId
     * @return mixed
     */
    protected function joinDefaultPriceAndWebsitesPrices($select, $websiteId)
    {
        $priceAttr   = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'price');
        $priceTable  = $priceAttr->getBackend()->getTable();
        $attributeId = $priceAttr->getId();

        $linkFields = $this->metadataPool
            ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();
        $select->join(
            ['e' => $this->resource->getTableName('catalog_product_entity')],
            sprintf('e.entity_id = rp.product_id'),
            []
        );
        $joinConditions = '%1$s.' . $linkFields . '=e.' . $linkFields . ' AND (%1$s.attribute_id='
            . $attributeId
            . ') and %1$s.store_id=%2$s';

        $select->join(
            ['pp_default' => $priceTable],
            sprintf($joinConditions, 'pp_default', \Magento\Store\Model\Store::DEFAULT_STORE_ID),
            []
        );

        $this->joinWebsitesPrices($select, $websiteId, $priceTable, $joinConditions);

        return $select;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param int $websiteId
     * @param string $priceTable
     * @param $joinCondition
     * @return mixed
     */
    protected function joinWebsitesPrices($select, $websiteId, $priceTable, $joinCondition)
    {
        $website      = $this->storeManager->getWebsite($websiteId);
        $defaultGroup = $website->getDefaultGroup();
        if ($defaultGroup instanceof \Magento\Store\Model\Group) {
            $storeId = $defaultGroup->getDefaultStoreId();
        } else {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        $select->joinInner(
            ['product_website' => $this->resource->getTableName('catalog_product_website')],
            'product_website.product_id=rp.product_id '
            . 'AND product_website.website_id = rp.website_id '
            . 'AND product_website.website_id='
            . $websiteId,
            []
        );

        $tableAlias = 'pp' . $websiteId;
        $select->joinLeft(
            [$tableAlias => $priceTable],
            sprintf($joinCondition, $tableAlias, $storeId),
            []
        );

        $select->joinInner(
            $this->tableNameCustomerEntity,
            sprintf('rp.customer_group_id = ' . $this->tableNameCustomerEntity . '.group_id'),
            []
        );

        $expression = $this->getExpression($tableAlias);

        $select->columns(
            [
                'default_price' => $this->connection->getIfNullSql($expression, 'pp_default.value'),
            ]
        );

        return $select;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param int $entityId
     * @param int $priceAttributeId
     * @return mixed
     */
    protected function joinMageworxDecimalPrice($select, $entityId, $priceAttributeId)
    {
        return $select->join(
            [
                'mageworx_decimal_price' => $this->resource->getTableName(
                    'mageworx_catalog_product_entity_decimal_customer_prices'
                )
            ],
            'mageworx_decimal_price.' . $entityId . ' = e.' . $entityId . ' 
            AND mageworx_decimal_price.attribute_id = ' . $priceAttributeId . ' 
            AND pp_default.store_id = mageworx_decimal_price.store_id
            AND mageworx_decimal_price.customer_id = ' . $this->tableNameCustomerEntity . '.entity_id',
            ['customer_id']
        );
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param int $entityId
     * @param int $specialPriceAttributeId
     * @return mixed
     */
    protected function joinMageworxDecimalSpecialPrice($select, $entityId, $specialPriceAttributeId)
    {
        /** @var \Magento\Framework\DB\Select $select */
        return $select->join(
            [
                'mageworx_decimal_special_price' => $this->resource->getTableName(
                    'mageworx_catalog_product_entity_decimal_customer_prices'
                )
            ],
            'mageworx_decimal_special_price.' . $entityId . ' = e.' . $entityId . ' 
            AND mageworx_decimal_special_price.attribute_id = ' . $specialPriceAttributeId . ' 
            AND pp_default.store_id = mageworx_decimal_special_price.store_id
            AND mageworx_decimal_special_price.customer_id = ' . $this->tableNameCustomerEntity . '.entity_id',
            []
        );
    }

    /**
     * @param $tableAlias
     * @return string
     */
    protected function getExpression($tableAlias)
    {
        return "IF(
                  mageworx_decimal_special_price.value IS NOT NULL, 
                  mageworx_decimal_special_price.value, 
                  IF(
                    mageworx_decimal_price.value IS NOT NULL, 
                    mageworx_decimal_price.value, 
                    " . $tableAlias . ".value
                  )
                )";
    }

}