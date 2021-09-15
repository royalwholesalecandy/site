<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Plugin\Indexer\MagentoTill225;

use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices as CustomerGroupPricesResourceModel;
use Magento\Framework\ObjectManagerInterface;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use MageWorx\CustomerGroupPrices\Helper\Group as HelperGroup;
use MageWorx\CustomerGroupPrices\Helper\UpdateTable as HelperUpdate;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Select;
use \Magento\Framework\EntityManager\EntityMetadataInterface;

class AddCustomerGroupPricesToProductPriceIndexPlugin extends DefaultPrice
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperGroup
     */
    protected $helperGroup;

    /**
     * @var HelperUpdate
     */
    protected $helperUpdate;

    /**
     * Core data
     *
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Magento\Framework\Indexer\Table\StrategyInterface;
     */
    protected $tableStrategy;

    /**
     * @var CustomerGroupPricesResourceModel
     */
    protected $customerGroupPricesResourceModel;

    /**
     * AddCustomerGroupPricesToProductPriceIndexPlugin constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param HelperData $helperData
     * @param HelperGroup $helperGroup
     * @param HelperUpdate $helperUpdate
     * @param CustomerGroupPricesResourceModel $customerGroupPricesResourceModel
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Module\Manager $moduleManager,
        HelperData $helperData,
        HelperGroup $helperGroup,
        HelperUpdate $helperUpdate,
        CustomerGroupPricesResourceModel $customerGroupPricesResourceModel,
        $connectionName = null
    ) {
        $this->eventManager                     = $eventManager;
        $this->moduleManager                    = $moduleManager;
        $this->helperData                       = $helperData;
        $this->helperGroup                      = $helperGroup;
        $this->helperUpdate                     = $helperUpdate;
        $this->tableStrategy                    = $tableStrategy;
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
        parent::__construct($context, $tableStrategy, $eavConfig, $eventManager, $moduleManager, $connectionName);
    }

    /**
     * @param DefaultPrice $object
     * @param callable $proceed
     *
     * @return DefaultPrice
     * @throws \Exception
     */
    public function aroundReindexAll(DefaultPrice $object, callable $proceed)
    {
        $this->tableStrategy->setUseIdxTable(true);
        $object->tableStrategy->setUseIdxTable(true);
        $object->beginTransaction();
        try {
            if ($this->helperData->isEnabledCustomerGroupPrice()) {
                $this->reindexPrice($object);
            } else {
                $object->reindex();
            }
            $object->commit();
        } catch (\Exception $e) {
            $object->rollBack();
            throw $e;
        }

        return $object;
    }

    /**
     * @param DefaultPrice $object
     * @param callable $proceed
     * @param array $entityIds
     *
     * @return DefaultPrice
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundReindexEntity(DefaultPrice $object, callable $proceed, $entityIds)
    {
        if ($this->helperData->isEnabledCustomerGroupPrice()) {
            $this->reindexPrice($object, $entityIds);
        } else {
            $object->reindex($entityIds);
        }

        return $object;
    }

    /**
     * @param DefaultPrice $object
     * @param array $entityIds
     *
     * @return DefaultPrice
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function reindexPrice(DefaultPrice $object, $entityIds = null)
    {
        if ($object->hasEntity() || !empty($entityIds)) {
            $this->prepareFinalPriceDataForType($entityIds, $object->getTypeId());
            $object->_applyCustomOption();
            $object->_movePriceDataToIndexTable();
        }

        return $object;
    }

    /**
     * @param array|int $entityIds
     * @param null|string $type
     *
     * @return $this
     * @throws \Exception
     */
    protected function prepareFinalPriceDataForType($entityIds, $type)
    {
        /* update table mageworx_catalog_product_entity_decimal_temp */
        $this->helperUpdate->updateTempTableMageWorxCatalogProductEntityDecimalTemp();
        $this->_prepareDefaultFinalPriceTable();

        $select = $this->getSelect($entityIds, $type);
        $query  = $select->insertFromSelect($this->_getDefaultFinalPriceTable(), [], false);
        $this->getConnection()->query($query);

        return $this;
    }

    /**
     * @param array $entityIds
     * @param null|string $type
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Exception
     */
    protected function getSelect($entityIds = null, $type = null)
    {
        $metadata   = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        );

        $price = $this->_addAttributeToSelectPrice(
            $select,
            'price',
            'e.' . $metadata->getLinkField(),
            'cs.store_id'
        );

        $select->join(
            ['cw' => $this->getTable('store_website')],
            '',
            ['website_id']
        )->join(
            ['cwd' => $this->_getWebsiteDateTable()],
            'cw.website_id = cwd.website_id',
            []
        )->join(
            ['csg' => $this->getTable('store_group')],
            'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id',
            []
        )->join(
            ['cs' => $this->getTable('store')],
            'csg.default_store_id = cs.store_id AND cs.store_id != 0',
            []
        )->join(
            ['pw' => $this->getTable('catalog_product_website')],
            'pw.product_id = e.entity_id AND pw.website_id = cw.website_id',
            []
        );

        $specialPrice = $this->_addAttributeToSelectPrice(
            $select,
            'special_price',
            'e.' . $metadata->getLinkField(),
            'cs.store_id'
        );

        $select->joinLeft(
            ['tp' => $this->_getTierPriceIndexTable()],
            'tp.entity_id = e.entity_id AND tp.website_id = cw.website_id' .
            ' AND tp.customer_group_id = ta_price.customer_group_id',
            []
        );

        if ($type !== null) {
            $select->where('e.type_id = ?', $type);
        }

        // add enable products limitation
        $statusCond = $connection->quoteInto(
            '=?',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );
        $this->_addAttributeToSelect(
            $select,
            'status',
            'e.' . $metadata->getLinkField(),
            'cs.store_id',
            $statusCond,
            true
        );
        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->_addAttributeToSelect(
                $select,
                'tax_class_id',
                'e.' . $metadata->getLinkField(),
                'cs.store_id'
            );
        } else {
            $taxClassId = new \Zend_Db_Expr('0');
        }
        $select->columns(['tax_class_id' => $taxClassId]);
        $currentDate     = $connection->getDatePartSql('cwd.website_date');
        $specialFrom     = $this->_addAttributeToSelect(
            $select,
            'special_from_date',
            'e.' . $metadata->getLinkField(),
            'cs.store_id'
        );
        $specialTo       = $this->_addAttributeToSelect(
            $select,
            'special_to_date',
            'e.' . $metadata->getLinkField(),
            'cs.store_id'
        );
        $specialToDate   = $connection->getDatePartSql($specialTo);
        $specialToUse    = $connection->getCheckSql("{$specialToDate} >= {$currentDate}", '1', '0');
        $specialFromDate = $connection->getDatePartSql($specialFrom);
        $specialFromUse  = $connection->getCheckSql("{$specialFromDate} <= {$currentDate}", '1', '0');
        $specialFromHas  = $connection->getCheckSql("{$specialFrom} IS NULL", '1', "{$specialFromUse}");
        $specialToHas    = $connection->getCheckSql("{$specialTo} IS NULL", '1', "{$specialToUse}");

        /* calculate final price */
        $finalPrice = $connection->getCheckSql(
            "{$specialFromHas} > 0 AND {$specialToHas} > 0" . " AND {$specialPrice} < {$price}",
            $specialPrice,
            $price
        );

        $this->addNewColumnsCondition($select, $connection, $price, $finalPrice);

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        $this->eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select'        => $select,
                'entity_field'  => new \Zend_Db_Expr('e.entity_id'),
                'website_field' => new \Zend_Db_Expr('cw.website_id'),
                'store_field'   => new \Zend_Db_Expr('cs.store_id')
            ]
        );

        return $select;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param float $price
     * @param float $finalPrice
     * @return \Magento\Framework\DB\Select
     */
    protected function addNewColumnsCondition($select, $connection, $price, $finalPrice)
    {
        $select->columns(
            [
                'orig_price' => $connection->getIfNullSql($price, 0),
                'price'      => $connection->getIfNullSql($finalPrice, 0),
                'min_price'  => $connection->getIfNullSql($finalPrice, 0),
                'max_price'  => $connection->getIfNullSql($finalPrice, 0),
                'tier_price' => new \Zend_Db_Expr('tp.min_price'),
                'base_tier'  => new \Zend_Db_Expr('tp.min_price'),
            ]
        );

        return $select;
    }

    /**
     * @param Select $select
     * @param string $attrCode
     * @param string|\Zend_Db_Expr $entity
     * @param string|\Zend_Db_Expr $store
     * @param \Zend_Db_Expr $condition
     * @param bool $required
     *
     * @return \Zend_Db_Expr
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addAttributeToSelectPrice(
        $select,
        $attrCode,
        $entity,
        $store,
        $condition = null,
        $required = false
    ) {
        $attribute               = $this->_getAttribute($attrCode);
        $attributeId             = $attribute->getAttributeId();
        $attributePriceId        = $this->customerGroupPricesResourceModel->getPriceAttributeId();
        $attributeSpecialPriceId = $this->customerGroupPricesResourceModel->getSpecialPriceAttributeId();

        if ($attributeId == $attributePriceId || $attributeId == $attributeSpecialPriceId) {
            $attributeTable =
                $this->_resources->getTableName('mageworx_catalog_product_entity_decimal_temp');
        } else {
            $attributeTable = $attribute->getBackend()->getTable();
        }

        $connection     = $this->getConnection();
        $joinType       = $condition !== null || $required ? 'join' : 'joinLeft';
        $productIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();

        if ($this->isGlobalAttribute($attribute)) {
            if ($attributeId == $attributePriceId) {
                $alias = 'ta_' . $attrCode;
                $this->joinAttributeTableWithAddCustomerGroupId(
                    $select,
                    $joinType,
                    $alias,
                    $attributeTable,
                    $productIdField,
                    $entity,
                    $attributeId
                );
            }
            if ($attributeId == $attributeSpecialPriceId) {
                $alias = 'ta_' . $attrCode;
                $this->joinAttributeTableWithConditions(
                    $select,
                    $joinType,
                    $alias,
                    $attributeTable,
                    $productIdField,
                    $entity,
                    $attributeId
                );
            }
            if ($attributeId != $attributePriceId && $attributeId != $attributeSpecialPriceId) {
                $alias = 'ta_' . $attrCode;
                $this->joinAttributeTable(
                    $select,
                    $joinType,
                    $alias,
                    $attributeTable,
                    $productIdField,
                    $entity,
                    $attributeId
                );
            }
            $expression = new \Zend_Db_Expr("{$alias}.value");
        } else {
            $dAlias = 'tad_' . $attrCode;
            $sAlias = 'tas_' . $attrCode;

            $this->joinAttributeTable(
                $select,
                $joinType,
                $dAlias,
                $attributeTable,
                $productIdField,
                $entity,
                $attributeId
            );

            $this->joinLeftAttributeTable(
                $select,
                $sAlias,
                $store,
                $attributeTable,
                $productIdField,
                $entity,
                $attributeId
            );
            $expression = $connection->getCheckSql(
                $connection->getIfNullSql("{$sAlias}.value_id", -1) . ' > 0',
                "{$sAlias}.value",
                "{$dAlias}.value"
            );
        }

        if ($condition !== null) {
            $select->where("{$expression}{$condition}");
        }

        return $expression;
    }

    /**
     * @param Select $select
     * @param string $joinType
     * @param string $alias
     * @param string $attributeTable
     * @param EntityMetadataInterface $productIdField
     * @param string $entity
     * @param int $attributeId
     * @return \Magento\Framework\DB\Select
     */
    protected function joinAttributeTable(
        $select,
        $joinType,
        $alias,
        $attributeTable,
        $productIdField,
        $entity,
        $attributeId
    ) {
        $select->{$joinType}(
            [$alias => $attributeTable],
            "{$alias}.{$productIdField} = {$entity} AND {$alias}.attribute_id = {$attributeId}" .
            " AND {$alias}.store_id = 0",
            []
        );

        return $select;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param string $joinType
     * @param string $alias
     * @param string $attributeTable
     * @param EntityMetadataInterface $productIdField
     * @param string $entity
     * @param int $attributeId
     * @return mixed
     */
    protected function joinAttributeTableWithAddCustomerGroupId(
        $select,
        $joinType,
        $alias,
        $attributeTable,
        $productIdField,
        $entity,
        $attributeId
    ) {
        $select->{$joinType}(
            [$alias => $attributeTable],
            "{$alias}.{$productIdField} = {$entity} AND {$alias}.attribute_id = {$attributeId}" .
            " AND {$alias}.store_id = 0",
            ['customer_group_id']
        );

        return $select;
    }

    /**
     * @param Select $select
     * @param string $joinType
     * @param string $alias
     * @param string $attributeTable
     * @param EntityMetadataInterface $productIdField
     * @param string $entity
     * @param int $attributeId
     * @return mixed
     */
    protected function joinAttributeTableWithConditions(
        $select,
        $joinType,
        $alias,
        $attributeTable,
        $productIdField,
        $entity,
        $attributeId
    ) {
        $select->{$joinType}(
            [$alias => $attributeTable],
            "{$alias}.{$productIdField} = {$entity} AND {$alias}.attribute_id = {$attributeId}" .
            " AND {$alias}.store_id = 0" . " AND {$alias}.customer_group_id = ta_price.customer_group_id",
            ['']
        );

        return $select;
    }

    /**
     * @param $select
     * @param string $sAlias
     * @param string $store
     * @param string $attributeTable
     * @param EntityMetadataInterface $productIdField
     * @param string $entity
     * @param int $attributeId
     * @return mixed
     */
    protected function joinLeftAttributeTable(
        $select,
        $sAlias,
        $store,
        $attributeTable,
        $productIdField,
        $entity,
        $attributeId
    ) {
        $select->joinLeft(
            [$sAlias => $attributeTable],
            "{$sAlias}.{$productIdField} = {$entity} AND {$sAlias}.attribute_id = {$attributeId}" .
            " AND {$sAlias}.store_id = {$store}",
            []
        );

        return $select;
    }

    /**
     * Define is global attribute
     * in magento 2.1.2 bug with $attribute->isScopeGlobal() and need add check getIsGlobal() == 2
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return bool
     */
    protected function isGlobalAttribute($attribute)
    {
        return ($attribute->isScopeGlobal() || $attribute->getIsGlobal() == 2);
    }
}