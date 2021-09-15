<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Plugin\Indexer\MagentoFrom226;

use \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\DB\Sql\ColumnValueExpression;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices as CustomerGroupPricesResourceModel;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use MageWorx\CustomerGroupPrices\Helper\UpdateTable as HelperUpdate;

class FinalPricePlugin
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var JoinAttributeProcessor
     */
    private $joinAttributeProcessor;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var HelperUpdate
     */
    protected $helperUpdate;

    /**
     * @var CustomerGroupPricesResourceModel
     */
    protected $customerGroupPricesResourceModel;

    /**
     * Core data
     *
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Mapping between dimensions and field in database
     *
     * @var array
     */
    private $dimensionToFieldMapper = [
        WebsiteDimensionProvider::DIMENSION_NAME       => 'pw.website_id',
        //CustomerGroupDimensionProvider::DIMENSION_NAME => 'cg.customer_group_id',
        CustomerGroupDimensionProvider::DIMENSION_NAME => 'ta_price.customer_group_id',
    ];

    /**
     * FinalPricePlugin constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param CustomerGroupPricesResourceModel $customerGroupPricesResourceModel
     * @param HelperData $helperData
     * @param HelperUpdate $helperUpdate
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        CustomerGroupPricesResourceModel $customerGroupPricesResourceModel,
        HelperData $helperData,
        HelperUpdate $helperUpdate,
        $connectionName = 'indexer'
    ) {
        $this->resource                         = $resource;
        $this->moduleManager                    = $moduleManager;
        $this->eventManager                     = $eventManager;
        $this->metadataPool                     = $metadataPool;
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
        $this->helperData                       = $helperData;
        $this->helperUpdate                     = $helperUpdate;

        $this->joinAttributeProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
            JoinAttributeProcessor::class
        );
    }

    /**
     * @param BaseFinalPrice $object
     * @param array $dimensions
     * @param string $productType
     * @param array $entityIds
     */
    public function beforeGetQuery(
        BaseFinalPrice $object,
        array $dimensions,
        string $productType,
        array $entityIds = []
    ) {
        if ($this->helperData->isEnabledCustomerGroupPrice()) {
            /* update table mageworx_catalog_product_entity_decimal_temp */
            $this->helperUpdate->updateTempTableMageWorxCatalogProductEntityDecimalTemp();
        }
    }

    /**
     * @param BaseFinalPrice $object
     * @param callable $proceed
     * @param array $dimensions
     * @param string $productType
     * @param array $entityIds
     * @return \Magento\Framework\DB\Select
     * @throws \Exception
     */
    public function aroundGetQuery(
        BaseFinalPrice $object,
        callable $proceed,
        array $dimensions,
        string $productType,
        array $entityIds = []
    ) {
        if (!$this->helperData->isEnabledCustomerGroupPrice()) {
            return $proceed($dimensions, $productType, $entityIds);
        }

        $connection = $this->getConnection();
        $metadata   = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $linkField  = $metadata->getLinkField();

        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        );
//        $select->joinInner(
//            ['cg' => $this->getTable('customer_group')],
//            array_key_exists(CustomerGroupDimensionProvider::DIMENSION_NAME, $dimensions)
//                ? sprintf(
//                '%s = %s',
//                $this->dimensionToFieldMapper[CustomerGroupDimensionProvider::DIMENSION_NAME],
//                $dimensions[CustomerGroupDimensionProvider::DIMENSION_NAME]->getValue()
//            ) : '',
//            ['customer_group_id']
//        );

        $price = $this->joinAttributeProcessor->process($select, 'price');

        $select->joinInner(
            ['pw' => $this->getTable('catalog_product_website')],
            'pw.product_id = e.entity_id',
            ['pw.website_id']
        );

        $select->joinInner(
            ['cwd' => $this->getTable('catalog_product_index_website')],
            'pw.website_id = cwd.website_id',
            []
        );
        $select->joinLeft(
        // we need this only for BCC in case someone expects table `tp` to be present in query
            ['tp' => $this->getTable('catalog_product_index_tier_price')],
            'tp.entity_id = e.entity_id AND' .
            //' tp.customer_group_id = cg.customer_group_id AND tp.website_id = pw.website_id',
            ' tp.customer_group_id = ta_price.customer_group_id AND tp.website_id = pw.website_id',
            []
        );
        $select->joinLeft(
        // calculate tier price specified as Website = `All Websites` and Customer Group = `Specific Customer Group`
            ['tier_price_1' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_1.' . $linkField . ' = e.' . $linkField . ' AND tier_price_1.all_groups = 0' .
            //' AND tier_price_1.customer_group_id = cg.customer_group_id AND tier_price_1.qty = 1' .
            ' AND tier_price_1.customer_group_id = ta_price.customer_group_id AND tier_price_1.qty = 1' .
            ' AND tier_price_1.website_id = 0',
            []
        );
        $select->joinLeft(
        // calculate tier price specified as Website = `Specific Website`
        //and Customer Group = `Specific Customer Group`
            ['tier_price_2' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_2.' . $linkField . ' = e.' . $linkField . ' AND tier_price_2.all_groups = 0 ' .
            //'AND tier_price_2.customer_group_id = cg.customer_group_id AND tier_price_2.qty = 1' .
            'AND tier_price_2.customer_group_id = ta_price.customer_group_id AND tier_price_2.qty = 1' .
            ' AND tier_price_2.website_id = pw.website_id',
            []
        );
        $select->joinLeft(
        // calculate tier price specified as Website = `All Websites` and Customer Group = `ALL GROUPS`
            ['tier_price_3' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_3.' . $linkField . ' = e.' . $linkField . ' AND tier_price_3.all_groups = 1 ' .
            'AND tier_price_3.customer_group_id = 0 AND tier_price_3.qty = 1 AND tier_price_3.website_id = 0',
            []
        );
        $select->joinLeft(
        // calculate tier price specified as Website = `Specific Website` and Customer Group = `ALL GROUPS`
            ['tier_price_4' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_4.' . $linkField . ' = e.' . $linkField . ' AND tier_price_4.all_groups = 1' .
            ' AND tier_price_4.customer_group_id = 0 AND tier_price_4.qty = 1' .
            ' AND tier_price_4.website_id = pw.website_id',
            []
        );

        /* move up define variable  $specialPrice */
        $specialPrice = $this->joinAttributeProcessor->process($select, 'special_price');

        foreach ($dimensions as $dimension) {
            if (!isset($this->dimensionToFieldMapper[$dimension->getName()])) {
                throw new \LogicException(
                    'Provided dimension is not valid for Price indexer: ' . $dimension->getName()
                );
            }
            $select->where($this->dimensionToFieldMapper[$dimension->getName()] . ' = ?', $dimension->getValue());
        }

        /* original check */
        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->joinAttributeProcessor->process($select, 'tax_class_id');
        } else {
            $taxClassId = new \Zend_Db_Expr(0);
        }
        $select->columns(['tax_class_id' => $taxClassId]);


        $this->joinAttributeProcessor->process($select, 'status', Status::STATUS_ENABLED);

        //$price        = $this->joinAttributeProcessor->process($select, 'price');
        //$specialPrice = $this->joinAttributeProcessor->process($select, 'special_price');
        $maxUnsignedBigint = '~0';
        $currentDate       = 'cwd.website_date';
        $specialTo         = $this->joinAttributeProcessor->process($select, 'special_to_date');
        $specialFrom       = $this->joinAttributeProcessor->process($select, 'special_from_date');


        /* calculate special */
        $specialToDate        = $connection->getDatePartSql($specialTo);
        $specialFromDate      = $connection->getDatePartSql($specialFrom);
        $specialToExpr        = "{$specialTo} IS NULL OR {$specialToDate} >= {$currentDate}";
        $specialPriceFromExpr = "{$specialFrom} IS NULL OR {$specialFromDate} <= {$currentDate}";
        $specialPriceExpr     = $connection->getCheckSql(
            "{$specialPrice} IS NOT NULL AND {$specialPriceFromExpr} AND {$specialToExpr}",
            $specialPrice,
            $maxUnsignedBigint
        );

        $magentoTierPrice   = $this->getTotalTierPriceExpression($price);
        $tierPriceExpr      = $connection->getIfNullSql($magentoTierPrice, $maxUnsignedBigint);
        $finalProductsPrice = $connection->getLeastSql(
            [
                $price,
                $specialPriceExpr,
                $tierPriceExpr,
            ]
        );

        $select->columns(
            [
                'price'       => $connection->getIfNullSql($price, 0),
                'final_price' => $connection->getIfNullSql($finalProductsPrice, 0),
                'min_price'   => $connection->getIfNullSql($finalProductsPrice, 0),
                'max_price'   => $connection->getIfNullSql($finalProductsPrice, 0),
                'tier_price'  => $magentoTierPrice,
            ]
        );

        $select->where("e.type_id = ?", $productType);

        if ($entityIds !== null) {
            if (count($entityIds) > 1) {
                $select->where(sprintf('e.entity_id BETWEEN %s AND %s', min($entityIds), max($entityIds)));
            } else {
                $select->where('e.entity_id = ?', $entityIds);
            }
        }

        $this->eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select'        => $select,
                'entity_field'  => new ColumnValueExpression('e.entity_id'),
                'website_field' => new ColumnValueExpression('pw.website_id'),
                'store_field'   => new ColumnValueExpression('cwd.default_store_id'),
            ]
        );

        return $select;
    }


    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = $this->resource->getConnection($this->connectionName);
        }

        return $this->connection;
    }

    /**
     * Get total tier price expression
     *
     * @param \Zend_Db_Expr $priceExpression
     * @return \Zend_Db_Expr
     */
    private function getTotalTierPriceExpression(\Zend_Db_Expr $priceExpression)
    {
        // const variable
        $unsignedBigint = '~0';

        return $this->getConnection()->getCheckSql(
            implode(
                ' AND ',
                [
                    'tier_price_1.value_id is NULL',
                    'tier_price_2.value_id is NULL',
                    'tier_price_3.value_id is NULL',
                    'tier_price_4.value_id is NULL'
                ]
            ),
            'NULL',
            $this->getConnection()->getLeastSql(
                [
                    $this->getConnection()->getIfNullSql(
                        $this->getTierPriceExpressionForTable('tier_price_1', $priceExpression),
                        $unsignedBigint
                    ),
                    $this->getConnection()->getIfNullSql(
                        $this->getTierPriceExpressionForTable('tier_price_2', $priceExpression),
                        $unsignedBigint
                    ),
                    $this->getConnection()->getIfNullSql(
                        $this->getTierPriceExpressionForTable('tier_price_3', $priceExpression),
                        $unsignedBigint
                    ),
                    $this->getConnection()->getIfNullSql(
                        $this->getTierPriceExpressionForTable('tier_price_4', $priceExpression),
                        $unsignedBigint
                    ),
                ]
            )
        );
    }

    /**
     * Get table
     *
     * @param string $tableName
     * @return string
     */
    private function getTable($tableName)
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }

    /**
     * Get tier price expression for table
     *
     * @param $tableAlias
     * @param \Zend_Db_Expr $priceExpression
     * @return \Zend_Db_Expr
     */
    private function getTierPriceExpressionForTable($tableAlias, \Zend_Db_Expr $priceExpression)
    {
        return $this->getConnection()->getCheckSql(
            sprintf('%s.value = 0', $tableAlias),
            sprintf(
                'ROUND(%s * (1 - ROUND(%s.percentage_value * cwd.rate, 4) / 100), 4)',
                $priceExpression,
                $tableAlias
            ),
            sprintf('ROUND(%s.value * cwd.rate, 4)', $tableAlias)
        );
    }
}