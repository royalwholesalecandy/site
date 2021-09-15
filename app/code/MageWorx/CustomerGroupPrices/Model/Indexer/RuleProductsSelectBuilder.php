<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Model\Indexer;

use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;

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
     * RuleProductsSelectBuilder constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param HelperData $helperData
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        HelperData $helperData
    ) {
        $this->eavConfig     = $eavConfig;
        $this->storeManager  = $storeManager;
        $this->metadataPool  = $metadataPool;
        $this->resource      = $resource;
        $this->objectManager = $objectmanager;
        $this->helperData    = $helperData;
    }

    /**
     * @param                                     $websiteId
     * @param \Magento\Catalog\Model\Product|null $product
     * @param bool $useAdditionalTable
     *
     * @return \Zend_Db_Statement_Interface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(
        $websiteId,
        \Magento\Catalog\Model\Product $product = null,
        $useAdditionalTable = false
    ) {
        $connection = $this->resource->getConnection();
        $indexTable = $this->resource->getTableName('catalogrule_product');

        if ($useAdditionalTable &&
            version_compare(
                $this->helperData->getModuleVersion(
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

        $select = $this->baseJoin($connection, $product, $indexTable);

        /* Join default price and websites prices to result */
        $this->joinDefaultPriceAndWebsitesPrices($select, $connection, $websiteId);

        return $connection->query($select);
    }

    /**
     * @param $connection
     * @param $product
     * @param $indexTable
     * @return mixed
     */
    protected function baseJoin($connection, $product, $indexTable)
    {
        $select = $connection->select()->from(
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
     * @param $select
     * @param $connection
     * @param int $websiteId
     * @return mixed
     */
    protected function joinDefaultPriceAndWebsitesPrices($select, $connection, $websiteId)
    {
        $priceAttr   = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'price');
        $priceTable  = $priceAttr->getBackend()->getTable();
        $attributeId = $priceAttr->getId();

        $linkField = $this->metadataPool
            ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();
        /** @var \Magento\Framework\DB\Select $select */
        $select->join(
            ['e' => $this->resource->getTableName('catalog_product_entity')],
            sprintf('e.entity_id = rp.product_id'),
            []
        );

        if ($this->helperData->isEnabledGroupPriceInCatalogPriceRule()) {
            $priceTable    = $this->resource->getTableName('mageworx_catalog_product_entity_decimal_temp');
            $joinCondition = '%1$s.' . $linkField . '=e.' . $linkField . ' AND (%1$s.attribute_id='
                . $attributeId
                . ') and %1$s.store_id=%2$s and %1$s.customer_group_id = rp.customer_group_id';
        } else {
            $joinCondition = '%1$s.' . $linkField . '=e.' . $linkField . ' AND (%1$s.attribute_id='
                . $attributeId
                . ') and %1$s.store_id=%2$s';
        }

        $select->join(
            ['pp_default' => $priceTable],
            sprintf($joinCondition, 'pp_default', \Magento\Store\Model\Store::DEFAULT_STORE_ID),
            []
        );

        $this->joinWebsitesPrice($select, $connection, $websiteId, $priceTable, $joinCondition);

        return $select;
    }

    /**
     * @param $select
     * @param $connection
     * @param int $websiteId
     * @param $priceTable
     * @param $joinCondition
     * @return mixed
     */
    protected function joinWebsitesPrice($select, $connection, $websiteId, $priceTable, $joinCondition)
    {
        $website      = $this->storeManager->getWebsite($websiteId);
        $defaultGroup = $website->getDefaultGroup();
        if ($defaultGroup instanceof \Magento\Store\Model\Group) {
            $storeId = $defaultGroup->getDefaultStoreId();
        } else {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select->joinInner(
            ['product_website' => $this->resource->getTableName('catalog_product_website')],
            'product_website.product_id = rp.product_id '
            . 'AND product_website.website_id = rp.website_id '
            . 'AND product_website.website_id = ' . $websiteId,
            []
        );

        $tableAlias = 'pp' . $websiteId;
        $select->joinLeft(
            [$tableAlias => $priceTable],
            sprintf($joinCondition, $tableAlias, $storeId),
            []
        );
        $select->columns(
            [
                'default_price' => $connection->getIfNullSql($tableAlias . '.value', 'pp_default.value'),
            ]
        );

        return $select;
    }

}