<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Plugin\Indexer\MagentoFrom226;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BasePriceModifier;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;

class BundlePricePlugin
{
    /**
     * @var IndexTableStructureFactory
     */
    private $indexTableStructureFactory;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var bool
     */
    private $fullReindexAction;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

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
     * @var BasePriceModifier
     */
    private $basePriceModifier;

    /**
     * @var JoinAttributeProcessor
     */
    private $joinAttributeProcessor;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @param MetadataPool $metadataPool
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param HelperData $helperData
     * @param bool $fullReindexAction
     * @param string $connectionName
     *
     */
    public function __construct(
        MetadataPool $metadataPool,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Module\Manager $moduleManager,
        HelperData $helperData,
        $fullReindexAction = false,
        $connectionName = 'indexer'
    ) {
        $this->metadataPool      = $metadataPool;
        $this->resource          = $resource;
        $this->eventManager      = $eventManager;
        $this->moduleManager     = $moduleManager;
        $this->helperData        = $helperData;
        $this->fullReindexAction = $fullReindexAction;
        $this->connectionName    = $connectionName;

        $this->indexTableStructureFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
            IndexTableStructureFactory::class
        );
        $this->tableMaintainer            = \Magento\Framework\App\ObjectManager::getInstance()->get(
            TableMaintainer::class
        );
        $this->basePriceModifier          = \Magento\Framework\App\ObjectManager::getInstance()->get(
            BasePriceModifier::class
        );
        $this->joinAttributeProcessor     = \Magento\Framework\App\ObjectManager::getInstance()->get(
            JoinAttributeProcessor::class
        );
    }

    public function aroundExecuteByDimensions(
        \Magento\Bundle\Model\ResourceModel\Indexer\Price $object,
        callable $proceed,
        array $dimensions,
        \Traversable $entityIds
    ) {
        if (!$this->helperData->isEnabledCustomerGroupPrice()) {
            return $proceed($dimensions, $entityIds);
        }

        $this->tableMaintainer->createMainTmpTable($dimensions);

        $temporaryPriceTable = $this->getTemporaryPriceTable($dimensions);
        $entityIds           = iterator_to_array($entityIds);
        $this->prepareTierPriceIndex($dimensions, $entityIds);
        $this->prepareBundlePriceTable();

        /*prepareBundlePriceByType by PRICE_TYPE_FIXED */
        $this->prepareBundlePriceByType(
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED,
            $dimensions,
            $entityIds
        );

        /*prepareBundlePriceByType by PRICE_TYPE_DYNAMIC */
        $this->prepareBundlePriceByType(
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC,
            $dimensions,
            $entityIds
        );


        $this->calculateBundleOptionPrice($temporaryPriceTable, $dimensions);

        $this->basePriceModifier->modifyPrice($temporaryPriceTable, $entityIds);
    }

    /**
     * @param array $dimensions
     * @return mixed
     */
    protected function getTemporaryPriceTable($dimensions)
    {
        return $this->indexTableStructureFactory->create(
            [
                'tableName'          => $this->tableMaintainer->getMainTmpTable($dimensions),
                'entityField'        => 'entity_id',
                'customerGroupField' => 'customer_group_id',
                'websiteField'       => 'website_id',
                'taxClassField'      => 'tax_class_id',
                'originalPriceField' => 'price',
                'finalPriceField'    => 'final_price',
                'minPriceField'      => 'min_price',
                'maxPriceField'      => 'max_price',
                'tierPriceField'     => 'tier_price',
            ]
        );
    }

    /**
     * Retrieve table name for temporary bundle selection prices index
     *
     * @return string
     */
    private function getBundleSelectionTable()
    {
        return $this->getTable('catalog_product_index_price_bundle_sel_tmp');
    }

    /**
     *
     * @return $this
     */
    private function prepareBundleSelectionTable()
    {
        $this->getConnection()->delete($this->getBundleSelectionTable());

        return $this;
    }

    /**
     *
     * @return $this
     */
    private function prepareBundleOptionTable()
    {
        $this->getConnection()->delete($this->getBundleOptionTable());

        return $this;
    }

    /**
     *
     * @return string
     */
    private function getBundleOptionTable()
    {
        return $this->getTable('catalog_product_index_price_bundle_opt_tmp');
    }

    /**
     *
     * @return string
     */
    private function getBundlePriceTable()
    {
        return $this->getTable('catalog_product_index_price_bundle_tmp');
    }

    /**
     *
     * @return $this
     */
    private function prepareBundlePriceTable()
    {
        $this->getConnection()->delete($this->getBundlePriceTable());

        return $this;
    }

    /**
     * Prepare temporary price index data for bundle products by price type
     *
     * @param array $dimensions
     * @param int $priceType
     * @param int|array $entityIds the entity ids limitation
     * @return void
     * @throws \Exception
     */
    private function prepareBundlePriceByType($priceType, array $dimensions, $entityIds = null)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        );

        $price = $this->joinAttributeProcessor->process($select, 'price');
//                                           ->joinInner(
//            ['cg' => $this->getTable('customer_group')],
//            array_key_exists(CustomerGroupDimensionProvider::DIMENSION_NAME, $dimensions)
//                ? sprintf(
//                '%s = %s',
//                $this->dimensionToFieldMapper[CustomerGroupDimensionProvider::DIMENSION_NAME],
//                $dimensions[CustomerGroupDimensionProvider::DIMENSION_NAME]->getValue()
//            ) : '',
//            ['customer_group_id']
//        )
        $select->joinInner(
            ['pw' => $this->getTable('catalog_product_website')],
            'pw.product_id = e.entity_id',
            ['pw.website_id']
        )->joinInner(
            ['cwd' => $this->getTable('catalog_product_index_website')],
            'pw.website_id = cwd.website_id',
            []
        );
        $select->joinLeft(
            ['tp' => $this->getTable('catalog_product_index_tier_price')],
            'tp.entity_id = e.entity_id AND tp.website_id = pw.website_id' .
            //' AND tp.customer_group_id = cg.customer_group_id',
            ' AND tp.customer_group_id = ta_price.customer_group_id',
            []
        );
        $select->where(
            'e.type_id=?',
            \Magento\Bundle\Ui\DataProvider\Product\Listing\Collector\BundlePrice::PRODUCT_TYPE
        );

        $specialPrice = $this->joinAttributeProcessor->process($select, 'special_price');

        foreach ($dimensions as $dimension) {
            if (!isset($this->dimensionToFieldMapper[$dimension->getName()])) {
                throw new \LogicException(
                    'Provided dimension is not valid for Price indexer: ' . $dimension->getName()
                );
            }
            $select->where($this->dimensionToFieldMapper[$dimension->getName()] . ' = ?', $dimension->getValue());
        }

        // rewrite joinAttributeProcessor->process in plugin
        $this->joinAttributeProcessor->process($select, 'status', Status::STATUS_ENABLED);
        /* default check */
        if (!$this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = new \Zend_Db_Expr('0');
        } else {
            $taxClassId = $this->joinAttributeProcessor->process($select, 'tax_class_id');
        }

        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            $select->columns(['tax_class_id' => new \Zend_Db_Expr('0')]);
        } else {
            $select->columns(
                ['tax_class_id' => $connection->getCheckSql($taxClassId . ' IS NOT NULL', $taxClassId, 0)]
            );
        }

        $this->joinAttributeProcessor->process($select, 'price_type', $priceType);

        //$price        = $this->joinAttributeProcessor->process($select, 'price');
        //$specialPrice = $this->joinAttributeProcessor->process($select, 'special_price');
        $specialFrom = $this->joinAttributeProcessor->process($select, 'special_from_date');
        $specialTo   = $this->joinAttributeProcessor->process($select, 'special_to_date');
        $currentDate = new \Zend_Db_Expr('cwd.website_date');

        $specialFromDate      = $connection->getDatePartSql($specialFrom);
        $specialPriceToDate   = $connection->getDatePartSql($specialTo);
        $specialPriceFromExpr = "{$specialFrom} IS NULL OR {$specialFromDate} <= {$currentDate}";
        $specialPriceToExpr   = "{$specialTo} IS NULL OR {$specialPriceToDate} >= {$currentDate}";
        $specialPriceExpr     = "{$specialPrice} IS NOT NULL AND {$specialPrice} > 0 AND {$specialPrice} < 100"
            . " AND {$specialPriceFromExpr} AND {$specialPriceToExpr}";
        $tierPriceExpr        = new \Zend_Db_Expr('tp.min_price');

        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
            $specialPriceExpr = $connection->getCheckSql(
                $specialPriceExpr,
                'ROUND(' . $price . ' * (' . $specialPrice . '  / 100), 4)',
                'NULL'
            );
            $tierPrice        = $connection->getCheckSql(
                $tierPriceExpr . ' IS NOT NULL',
                'ROUND((1 - ' . $tierPriceExpr . ' / 100) * ' . $price . ', 4)',
                'NULL'
            );
            $finalPrice       = $connection->getLeastSql(
                [
                    $price,
                    $connection->getIfNullSql($specialPriceExpr, $price),
                    $connection->getIfNullSql($tierPrice, $price),
                ]
            );
        } else {
            $finalPrice = new \Zend_Db_Expr('0');
            $tierPrice  = $connection->getCheckSql($tierPriceExpr . ' IS NOT NULL', '0', 'NULL');
        }

        $select->columns(
            [
                'price_type'    => new \Zend_Db_Expr($priceType),
                'special_price' => $connection->getCheckSql($specialPriceExpr, $specialPrice, '0'),
                'tier_percent'  => $tierPriceExpr,
                'orig_price'    => $connection->getIfNullSql($price, '0'),
                'price'         => $finalPrice,
                'min_price'     => $finalPrice,
                'max_price'     => $finalPrice,
                'tier_price'    => $tierPrice,
                'base_tier'     => $tierPrice,
            ]
        );

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        $this->eventManager->dispatch(
            'catalog_product_prepare_index_select',
            [
                'select'        => $select,
                'entity_field'  => new \Zend_Db_Expr('e.entity_id'),
                'website_field' => new \Zend_Db_Expr('pw.website_id'),
                'store_field'   => new \Zend_Db_Expr('cwd.default_store_id')
            ]
        );

        $query = $select->insertFromSelect($this->getBundlePriceTable());
        $connection->query($query);
    }

    /**
     * Calculate bundle product selections price by product type
     *
     * @param array $dimensions
     * @param int $priceType
     * @return void
     * @throws \Exception
     */
    private function calculateBundleSelectionPrice($dimensions, $priceType)
    {
        $connection = $this->getConnection();

        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
            $selectionPriceValue = $connection->getCheckSql(
                'bsp.selection_price_value IS NULL',
                'bs.selection_price_value',
                'bsp.selection_price_value'
            );
            $selectionPriceType  = $connection->getCheckSql(
                'bsp.selection_price_type IS NULL',
                'bs.selection_price_type',
                'bsp.selection_price_type'
            );
            $priceExpr           = new \Zend_Db_Expr(
                $connection->getCheckSql(
                    $selectionPriceType . ' = 1',
                    'ROUND(i.price * (' . $selectionPriceValue . ' / 100),4)',
                    $connection->getCheckSql(
                        'i.special_price > 0 AND i.special_price < 100',
                        'ROUND(' . $selectionPriceValue . ' * (i.special_price / 100),4)',
                        $selectionPriceValue
                    )
                ) . '* bs.selection_qty'
            );

            $tierPriceExpr = $connection->getCheckSql(
                'i.base_tier IS NOT NULL',
                $connection->getCheckSql(
                    $selectionPriceType . ' = 1',
                    'ROUND(i.base_tier - (i.base_tier * (' . $selectionPriceValue . ' / 100)),4)',
                    $connection->getCheckSql(
                        'i.tier_percent > 0',
                        'ROUND((1 - i.tier_percent / 100) * ' . $selectionPriceValue . ',4)',
                        $selectionPriceValue
                    )
                ) . ' * bs.selection_qty',
                'NULL'
            );

            $priceExpr = $connection->getLeastSql(
                [
                    $priceExpr,
                    $connection->getIfNullSql($tierPriceExpr, $priceExpr),
                ]
            );
        } else {
            $price            = 'idx.min_price * bs.selection_qty';
            $specialPriceExpr = $connection->getCheckSql(
                'i.special_price > 0 AND i.special_price < 100',
                'ROUND(' . $price . ' * (i.special_price / 100), 4)',
                $price
            );
            $tierPriceExpr    = $connection->getCheckSql(
                'i.tier_percent IS NOT NULL',
                'ROUND((1 - i.tier_percent / 100) * ' . $price . ', 4)',
                'NULL'
            );
            $priceExpr        = $connection->getLeastSql(
                [
                    $specialPriceExpr,
                    $connection->getIfNullSql($tierPriceExpr, $price),
                ]
            );
        }

        $metadata  = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $select    = $connection->select()->from(
            ['i' => $this->getBundlePriceTable()],
            ['entity_id', 'customer_group_id', 'website_id']
        );
        $select->join(
            ['parent_product' => $this->getTable('catalog_product_entity')],
            'parent_product.entity_id = i.entity_id',
            []
        );
        $select->join(
            ['bo' => $this->getTable('catalog_product_bundle_option')],
            "bo.parent_id = parent_product.$linkField",
            ['option_id']
        );
        $select->join(
            ['bs' => $this->getTable('catalog_product_bundle_selection')],
            'bs.option_id = bo.option_id',
            ['selection_id']
        );
        $select->joinLeft(
            ['bsp' => $this->getTable('catalog_product_bundle_selection_price')],
            'bs.selection_id = bsp.selection_id AND bsp.website_id = i.website_id',
            ['']
        );
        $select->join(
            ['idx' => $this->getMainTable($dimensions)],
            'bs.product_id = idx.entity_id AND i.customer_group_id = idx.customer_group_id' .
            ' AND i.website_id = idx.website_id',
            []
        );
        $select->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'bs.product_id = e.entity_id AND e.required_options=0',
            []
        )->where(
            'i.price_type=?',
            $priceType
        );
        $select->columns(
            [
                'group_type'  => $connection->getCheckSql("bo.type = 'select' OR bo.type = 'radio'", '0', '1'),
                'is_required' => 'bo.required',
                'price'       => $priceExpr,
                'tier_price'  => $tierPriceExpr,
            ]
        );

        $query = $select->insertFromSelect($this->getBundleSelectionTable());
        $connection->query($query);
    }

    /**
     * Prepare percentage tier price for bundle products
     *
     * @param array $dimensions
     * @param array $entityIds
     * @return void
     * @throws \Exception
     */
    private function prepareTierPriceIndex($dimensions, $entityIds)
    {
        $connection = $this->getConnection();
        $metadata   = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField  = $metadata->getLinkField();
        // remove index by bundle products
        $select = $connection->select()->from(
            ['i' => $this->getTable('catalog_product_index_tier_price')],
            null
        );
        $select->join(
            ['e' => $this->getTable('catalog_product_entity')],
            "i.entity_id=e.entity_id",
            []
        )->where(
            'e.type_id=?',
            \Magento\Bundle\Ui\DataProvider\Product\Listing\Collector\BundlePrice::PRODUCT_TYPE
        );
        $query = $select->deleteFromSelect('i');
        $connection->query($query);

        $select = $connection->select()->from(
            ['tp' => $this->getTable('catalog_product_entity_tier_price')],
            ['e.entity_id']
        );
        $select->join(
            ['e' => $this->getTable('catalog_product_entity')],
            "tp.{$linkField} = e.{$linkField}",
            []
        );
        $select->join(
            ['cg' => $this->getTable('customer_group')],
            'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)',
            ['customer_group_id']
        );
        $select->join(
            ['pw' => $this->getTable('store_website')],
            'tp.website_id = 0 OR tp.website_id = pw.website_id',
            ['website_id']
        );
        $select->where(
            'pw.website_id != 0'
        );
        $select->where(
            'e.type_id=?',
            \Magento\Bundle\Ui\DataProvider\Product\Listing\Collector\BundlePrice::PRODUCT_TYPE
        );
        $select->columns(
            new \Zend_Db_Expr('MIN(tp.value)')
        );
        $select->group(
            ['e.entity_id', 'cg.customer_group_id', 'pw.website_id']
        );

        if (!empty($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        foreach ($dimensions as $dimension) {
            if (!isset($this->dimensionToFieldMapper[$dimension->getName()])) {
                throw new \LogicException(
                    'Provided dimension is not valid for Price indexer: ' . $dimension->getName()
                );
            }
            $select->where($this->dimensionToFieldMapper[$dimension->getName()] . ' = ?', $dimension->getValue());
        }

        $query = $select->insertFromSelect($this->getTable('catalog_product_index_tier_price'));
        $connection->query($query);
    }

    /**
     *
     * @param IndexTableStructure $priceTable
     * @param array $dimensions
     *
     * @return void
     * @throws \Exception
     */
    private function calculateBundleOptionPrice($priceTable, $dimensions)
    {
        $connection = $this->getConnection();

        $this->prepareBundleSelectionTable();
        $this->calculateBundleSelectionPrice($dimensions, \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED);
        $this->calculateBundleSelectionPrice($dimensions, \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC);

        $this->prepareBundleOptionTable();

        $select = $connection->select()->from(
            $this->getBundleSelectionTable(),
            ['entity_id', 'customer_group_id', 'website_id', 'option_id']
        );
        $select->group(
            ['entity_id', 'customer_group_id', 'website_id', 'option_id']
        );

        $minimumPrice = $connection->getCheckSql('is_required = 1', 'price', 'NULL');
        $tierPrice    = $connection->getCheckSql('is_required = 1', 'tier_price', 'NULL');

        $select->columns(
            [
                'min_price'      => new \Zend_Db_Expr('MIN(' . $minimumPrice . ')'),
                'alt_price'      => new \Zend_Db_Expr('MIN(price)'),
                'max_price'      => $connection->getCheckSql('group_type = 0', 'MAX(price)', 'SUM(price)'),
                'tier_price'     => new \Zend_Db_Expr('MIN(' . $tierPrice . ')'),
                'alt_tier_price' => new \Zend_Db_Expr('MIN(tier_price)'),
            ]
        );

        $query = $select->insertFromSelect($this->getBundleOptionTable());
        $connection->query($query);

        $this->getConnection()->delete($priceTable->getTableName());
        $this->applyBundlePrice($priceTable);
        $this->applyBundleOptionPrice($priceTable);
    }

    /**
     * @param IndexTableStructure $priceTable
     */
    private function applyBundleOptionPrice($priceTable)
    {
        $connection = $this->getConnection();

        $subSelect = $connection->select()->from(
            $this->getBundleOptionTable(),
            [
                'entity_id',
                'customer_group_id',
                'website_id',
                'min_price'      => new \Zend_Db_Expr('SUM(min_price)'),
                'alt_price'      => new \Zend_Db_Expr('MIN(alt_price)'),
                'max_price'      => new \Zend_Db_Expr('SUM(max_price)'),
                'tier_price'     => new \Zend_Db_Expr('SUM(tier_price)'),
                'alt_tier_price' => new \Zend_Db_Expr('MIN(alt_tier_price)'),
            ]
        )->group(
            ['entity_id', 'customer_group_id', 'website_id']
        );

        $minimumPrice = 'i.min_price + ' . $connection->getIfNullSql('io.min_price', '0');
        $tierPrice    = 'i.tier_price + ' . $connection->getIfNullSql('io.tier_price', '0');
        $select       = $connection->select()->join(
            ['io' => $subSelect],
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id',
            []
        )->columns(
            [
                'min_price'  => $connection->getCheckSql("{$minimumPrice} = 0", 'io.alt_price', $minimumPrice),
                'max_price'  => new \Zend_Db_Expr('io.max_price + i.max_price'),
                'tier_price' => $connection->getCheckSql("{$tierPrice} = 0", 'io.alt_tier_price', $tierPrice),
            ]
        );

        $query = $select->crossUpdateFromSelect(['i' => $priceTable->getTableName()]);
        $connection->query($query);
    }

    /**
     * Get main table
     *
     * @param array $dimensions
     * @return string
     */
    private function getMainTable($dimensions)
    {
        if ($this->fullReindexAction) {
            return $this->tableMaintainer->getMainReplicaTable($dimensions);
        }

        return $this->tableMaintainer->getMainTable($dimensions);
    }

    /**
     * Get connection
     *
     * return \Magento\Framework\DB\Adapter\AdapterInterface
     *
     * @throws \DomainException
     */
    private function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = $this->resource->getConnection($this->connectionName);
        }

        return $this->connection;
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
     * Apply Bundle Price
     *
     * @param IndexTableStructure $priceTable
     */
    private function applyBundlePrice($priceTable)
    {
        $select = $this->getConnection()->select();

        $select->from(
            $this->getBundlePriceTable(),
            [
                'entity_id',
                'customer_group_id',
                'website_id',
                'tax_class_id',
                'orig_price',
                'price',
                'min_price',
                'max_price',
                'tier_price',
            ]
        );

        $query = $select->insertFromSelect($priceTable->getTableName());
        $this->getConnection()->query($query);
    }
}