<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Plugin\Indexer\MagentoFrom226;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices as CustomerGroupPricesResourceModel;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;

class AttributeProcessPlugin
{

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var CustomerGroupPricesResourceModel
     */
    protected $customerGroupPricesResourceModel;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param CustomerGroupPricesResourceModel $customerGroupPricesResourceModel
     * @param HelperData $helperData
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\App\ResourceConnection $resource,
        CustomerGroupPricesResourceModel $customerGroupPricesResourceModel,
        HelperData $helperData,
        $connectionName = 'indexer'
    ) {
        $this->eavConfig                        = $eavConfig;
        $this->metadataPool                     = $metadataPool;
        $this->resource                         = $resource;
        $this->connectionName                   = $connectionName;
        $this->helperData                       = $helperData;
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
    }

    /**
     * @param Select $select
     * @param string $attributeCode
     * @param string|null $attributeValue
     * @return \Zend_Db_Expr
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function aroundProcess(
        JoinAttributeProcessor $object,
        callable $proceed,
        Select $select,
        $attributeCode,
        $attributeValue = null
    ) {
        if (!$this->helperData->isEnabledCustomerGroupPrice()) {
            return $proceed($select, $attributeCode, $attributeValue);
        }

        $attribute   = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        $attributeId = $attribute->getAttributeId();

        $attributePriceId        = $this->customerGroupPricesResourceModel->getPriceAttributeId();
        $attributeSpecialPriceId = $this->customerGroupPricesResourceModel->getSpecialPriceAttributeId();

        if ($attributeId == $attributePriceId || $attributeId == $attributeSpecialPriceId) {
            $attributeTable = $this->resource->getTableName('mageworx_catalog_product_entity_decimal_temp');
        } else {
            $attributeTable = $attribute->getBackend()->getTable();
        }

        $connection     = $this->resource->getConnection($this->connectionName);
        $joinType       = $attributeValue !== null ? 'join' : 'joinLeft';
        $productIdField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        if ($attribute->isScopeGlobal()) {
            $alias = 'ta_' . $attributeCode;
            if ($attributeId == $attributePriceId) {
                $select->{$joinType}(
                    [$alias => $attributeTable],
                    "{$alias}.{$productIdField} = e.{$productIdField} AND {$alias}.attribute_id = {$attributeId}" .
                    " AND {$alias}.store_id = 0",
                    ['customer_group_id']
                );
            }
            if ($attributeId == $attributeSpecialPriceId) {
                $select->{$joinType}(
                    [$alias => $attributeTable],
                    "{$alias}.{$productIdField} = e.{$productIdField} AND {$alias}.attribute_id = {$attributeId}" .
                    " AND {$alias}.store_id = 0" . " AND {$alias}.customer_group_id = ta_price.customer_group_id",
                    ['']
                );
            }

            if ($attributeId != $attributePriceId && $attributeId != $attributeSpecialPriceId) {
                $select->{$joinType}(
                    [$alias => $attributeTable],
                    "{$alias}.{$productIdField} = e.{$productIdField} AND {$alias}.attribute_id = {$attributeId}" .
                    " AND {$alias}.store_id = 0",
                    []
                );
            }

            $whereExpression = new Expression("{$alias}.value");
        } else {
            $dAlias = 'tad_' . $attributeCode;
            $select->{$joinType}(
                [$dAlias => $attributeTable],
                "{$dAlias}.{$productIdField} = e.{$productIdField} AND {$dAlias}.attribute_id = {$attributeId}" .
                " AND {$dAlias}.store_id = 0",
                []
            );

            $sAlias = 'tas_' . $attributeCode;
            $select->joinLeft(
                [$sAlias => $attributeTable],
                "{$sAlias}.{$productIdField} = e.{$productIdField} AND {$sAlias}.attribute_id = {$attributeId}" .
                " AND {$sAlias}.store_id = cwd.default_store_id",
                []
            );

            $whereExpression = $connection->getCheckSql(
                $connection->getIfNullSql("{$sAlias}.value_id", -1) . ' > 0',
                "{$sAlias}.value",
                "{$dAlias}.value"
            );
        }

        if ($attributeValue !== null) {
            $select->where("{$whereExpression} = ?", $attributeValue);
        }

        return $whereExpression;
    }
}