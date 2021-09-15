<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Model\ResourceModel\Product\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use MageWorx\CustomerPrices\Helper\Customer as HelperCustomer;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection as AppResource;

class CustomerPrice extends DefaultPrice
{
    /**
     * Product type code
     *
     * @var string
     */
    protected $_typeId;

    /**
     * Product Type is composite flag
     *
     * @var bool
     */
    protected $isComposite = false;

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
     * @var bool|null
     */
    private $hasEntity = null;

    /**
     * @var HelperCustomer
     */
    protected $helperCustomer;

    /**
     * @var HelperCalculate
     */
    protected $helperCalculate;

    /**
     * @var ResourceCustomerPrices
     */
    protected $customerPricesResourceModel;

    /**
     * CustomerPrice constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param HelperCustomer $helperCustomer
     * @param HelperCalculate $helperCalculate
     * @param ResourceCustomerPrices $customerPricesResourceModel
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Module\Manager $moduleManager,
        HelperCustomer $helperCustomer,
        HelperCalculate $helperCalculate,
        ResourceCustomerPrices $customerPricesResourceModel,
        $connectionName = null
    ) {
        $this->eventManager                = $eventManager;
        $this->moduleManager               = $moduleManager;
        $this->helperCustomer              = $helperCustomer;
        $this->helperCalculate             = $helperCalculate;
        $this->customerPricesResourceModel = $customerPricesResourceModel;
        parent::__construct($context, $tableStrategy, $eavConfig, $eventManager, $moduleManager, $connectionName);
    }

    /**
     * Define main price index table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageworx_catalog_product_index_price', 'entity_id');
    }

    /**
     * Reindex temporary (price result data) for all products
     *
     * @return $this
     * @throws \Exception
     */
    public function reindexAll()
    {
        $this->tableStrategy->setUseIdxTable(true);
        $this->beginTransaction();
        try {
            $this->reindex();
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * @param array $entityIds
     * @param array $customerIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reindexEntityCustomer($entityIds, $customerIds)
    {
        $this->reindexCustomer($entityIds, $customerIds);

        return $this;
    }

    /**
     * @param array $entityIds
     * @param array $customerIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function reindexCustomer($entityIds, $customerIds)
    {
        if (!empty($entityIds) || $this->hasEntity()) {
            $this->_prepareFinalPriceDataCustomer($entityIds, $customerIds);
            $this->_applyCustomOption();
            $this->_movePriceDataToIndexTable($entityIds);
        }

        return $this;
    }

    /**
     * Prepare products default final price in temporary index table
     *
     * @param array $entityIds
     * @param $type
     * @param array $customerIds
     * @return $this
     * @throws \Exception
     */
    protected function prepareFinalPriceDataForTypeCustomer($entityIds, $type, $customerIds)
    {
        $this->_prepareDefaultFinalPriceTable();
        $this->prepareMageWorxCatalogProductEntityDecimalCustomerPrice($entityIds, $customerIds);

        $select = $this->getSelect($entityIds, $type);
        $query  = $select->insertFromSelect($this->_getDefaultFinalPriceTable(), [], false);
        $this->getConnection()->query($query);

        return $this;
    }

    /**
     * Prepare table mageworx_catalog_product_entity_decimal_customer_prices
     *
     * @param array $entityIds
     * @param array $customerIds
     */
    protected function prepareMageWorxCatalogProductEntityDecimalCustomerPrice($entityIds, $customerIds)
    {
        $connection              = $this->getConnection();
        $id                      = $this->helperCalculate->getLinkField();
        $priceAttributeId        = $this->customerPricesResourceModel->getPriceAttributeId();
        $specialPriceAttributeId = $this->customerPricesResourceModel->getSpecialPriceAttributeId();

        $originalTable       = $this->_resources->getTableName('catalog_product_entity_decimal');
        $tableCustomerPrices = $this->_resources->getTableName('mageworx_customerprices');
        $select              = $connection->select()->from($originalTable)->where(
            $originalTable . '.' . $id . ' = ?',
            $entityIds
        );

        $cond = 'customerprices.product_id = ' . $originalTable . '.' . $id;
        $select->joinLeft(
            ['customerprices' => $tableCustomerPrices],
            $cond
        );
        if (!empty($customerIds)) {
            $select->where('customerprices.customer_id IN(?)', $customerIds);
        }

        $newPrice = "IFNULL(
                        IF(
                            customerprices.price_value IS NULL,
                            NULL,
                            IF(
                               " . $originalTable . ".attribute_id != " . $priceAttributeId . ",
                               NULL,
                               IF(
                                  customerprices.price_type = 1,
                                  IF(
                                    customerprices.price_sign = '+',
                                    " . $originalTable . ".value  + customerprices.price_value,
                                    IF(
                                      customerprices.price_sign = '-',
                                      IF(
                                        " . $originalTable . ".value  - customerprices.price_value < 0,
                                        0,
                                        " . $originalTable . ".value  - customerprices.price_value
                                      ),
                                      IF(
                                        customerprices.price_value < 0,
                                        0,
                                        customerprices.price_value
                                      )
                                    )
                                  ),
                                  IF(
                                    customerprices.price_sign = '+',
                                    IF(
                                      " . $originalTable . ".value + 
                                      " . $originalTable . ".value * (customerprices.price_value/100) < 0,
                                      0,
                                      " . $originalTable . ".value + 
                                      " . $originalTable . ".value * (customerprices.price_value/100)
                                    ),
                                    IF(
                                      customerprices.price_sign = '-',
                                      IF(
                                        " . $originalTable . ".value - 
                                        " . $originalTable . ".value * (customerprices.price_value/100) < 0,
                                        0,
                                        " . $originalTable . ".value - 
                                        " . $originalTable . ".value * (customerprices.price_value/100)
                                      ),
                                      IF(
                                        " . $originalTable . ".value * (customerprices.price_value/100) < 0,
                                        0,
                                        " . $originalTable . ".value * (customerprices.price_value/100)
                                      )
                                    )
                                  ) 
                               )
                            )
                        ),
                        IFNULL(
                            IF(
                                customerprices.special_price_value IS NULL,
                                NULL,
                                IF(
                                   " . $originalTable . ".attribute_id != " . $specialPriceAttributeId . ",
                                   NULL,
                                   IF(
                                      customerprices.special_price_type = 1,
                                      IF(
                                        customerprices.special_price_sign = '+',
                                        IF(
                                          " . $originalTable . ".value + customerprices.special_price_value < 0,
                                          0,
                                          " . $originalTable . ".value + customerprices.special_price_value
                                        ),
                                        IF(
                                          customerprices.special_price_sign = '-',
                                          IF(
                                            " . $originalTable . ".value - customerprices.special_price_value < 0,
                                            0,
                                            " . $originalTable . ".value - customerprices.special_price_value
                                            ),
                                           customerprices.special_price_value
                                        )
                                      ),
                                      IF(
                                        customerprices.special_price_sign = '+',
                                        IF(
                                          " . $originalTable . ".value  + 
                                          " . $originalTable . ".value * (customerprices.special_price_value/100) < 0,
                                          0,
                                          " . $originalTable . ".value  + 
                                          " . $originalTable . ".value * (customerprices.special_price_value/100)
                                        ),
                                        IF(
                                          customerprices.special_price_sign = '-',
                                          IF(
                                            " . $originalTable . ".value  - 
                                            " . $originalTable . ".value * (customerprices.special_price_value/100) < 0,
                                            0,
                                            " . $originalTable . ".value  - 
                                            " . $originalTable . ".value * (customerprices.special_price_value/100)
                                          ),
                                          IF(
                                            " . $originalTable . ".value * (customerprices.special_price_value/100) < 0,
                                            0,
                                            " . $originalTable . ".value * (customerprices.special_price_value/100)
                                          )
                                        )
                                      ) 
                                   )
                                )
                            ),
                            " . $originalTable . ".value
                        )                     
                    )";

        $select->reset(\Zend_Db_Select::COLUMNS)
               ->columns(
                   array(
                       'value_id',
                       'attribute_id' => $originalTable . '.attribute_id',
                       'store_id'     => $originalTable . '.store_id',
                       $id            => $originalTable . '.' . $id,
                       'value'        => new \Zend_Db_Expr($newPrice),
                       'customer_id'  => 'customerprices.customer_id'
                   )
               );

        $newTable = $this->_resources->getTableName('mageworx_catalog_product_entity_decimal_customer_prices');
        $table    = $this->getTable($newTable);

        /* clean old data */
        $where = ['customer_id IN(?)' => $customerIds, $id . ' IN(?)' => $entityIds];
        $connection->delete($table, $where);

        $query = $select->insertFromSelect($newTable, [], false);
        $connection->query($query);
    }

    /**
     * @param array|null $entityIds
     * @param null $type
     * @return \Magento\Framework\DB\Select
     * @throws \Exception
     */
    protected function getSelect($entityIds = null, $type = null)
    {
        $metadata   = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $connection = $this->getConnection();
        $select     = $this->joinBaseTable($connection);

        if ($type !== null) {
            $select->where('e.type_id = ?', $type);
        }

        // add enable products limitation
        $statusCond = $connection->quoteInto(
            ' =?',
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

        $newPrice = $this->_addAttributeToSelectPriceCustomer(
            $select,
            'price',
            'e.' . $metadata->getLinkField(),
            'cs.store_id'
        );

        $newSpecialPrice = $this->_addAttributeToSelectPriceCustomer(
            $select,
            'special_price',
            'e.' . $metadata->getLinkField(),
            'cs.store_id'
        );
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
        $specialFromDate = $connection->getDatePartSql($specialFrom);
        $specialToDate   = $connection->getDatePartSql($specialTo);

        /* calculate price and finalPrice */
        $specialFromUse = $connection->getCheckSql("{$specialFromDate} <= {$currentDate}", '1', '0');
        $specialToUse   = $connection->getCheckSql("{$specialToDate} >= {$currentDate}", '1', '0');
        $specialFromHas = $connection->getCheckSql("{$specialFrom} IS NULL", '1', "{$specialFromUse}");
        $specialToHas   = $connection->getCheckSql("{$specialTo} IS NULL", '1', "{$specialToUse}");
        $finalPrice     = $connection->getCheckSql(
            "{$specialFromHas} > 0 AND {$specialToHas} > 0" . " AND {$newSpecialPrice} < {$newPrice}",
            $newSpecialPrice,
            $newPrice
        );

        $this->addNewColumnInTable($select, $connection, $newPrice, $finalPrice);

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }
        $select->group('ta_price.customer_id');

        return $select;
    }

    /**
     * @param $select
     * @param $connection
     * @param $price
     * @param $finalPrice
     * @return \Magento\Framework\DB\Select
     */
    protected function addNewColumnInTable($select, $connection, $price, $finalPrice)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select->columns(
            [
                'orig_price'  => $connection->getIfNullSql($price, 0),
                'price'       => $connection->getIfNullSql($finalPrice, 0),
                'min_price'   => $connection->getIfNullSql($finalPrice, 0),
                'max_price'   => $connection->getIfNullSql($finalPrice, 0),
                'customer_id' => 'ta_price.customer_id'
            ]
        );

        return $select;
    }

    /**
     * @param $connection
     * @return \Magento\Framework\DB\Select
     */
    protected function joinBaseTable($connection)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->join(
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

        return $select;
    }


    /**
     * Retrieve table name for custom option temporary aggregation data
     *
     * @return string
     */
    protected function _getCustomOptionAggregateTable()
    {
        return $this->tableStrategy->getTableName('mageworx_catalog_product_index_price_opt_agr');
    }

    /**
     * Retrieve table name for custom option prices data
     *
     * @return string
     */
    protected function _getCustomOptionPriceTable()
    {
        return $this->tableStrategy->getTableName('mageworx_catalog_product_index_price_opt');
    }

    /**
     * Prepare table structure for custom option temporary aggregation data
     *
     * @return $this
     */
    protected function _prepareCustomOptionAggregateTable()
    {
        $this->getConnection()->delete($this->_getCustomOptionAggregateTable());

        return $this;
    }

    /**
     * Prepare table structure for custom option prices data
     *
     * @return $this
     */
    protected function _prepareCustomOptionPriceTable()
    {
        $this->getConnection()->delete($this->_getCustomOptionPriceTable());

        return $this;
    }

    /**
     * Apply custom option minimal and maximal price to temporary final price index table
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _applyCustomOption()
    {
        $connection = $this->getConnection();
        $coaTable   = $this->_getCustomOptionAggregateTable();
        $copTable   = $this->_getCustomOptionPriceTable();

        $this->_prepareCustomOptionAggregateTable();
        $this->_prepareCustomOptionPriceTable();

        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->joinCoaTableForApplyCustomOption($connection);

        $optPriceType  = $connection->getCheckSql(
            'otps.option_type_price_id > 0',
            'otps.price_type',
            'otpd.price_type'
        );
        $optPriceValue = $connection->getCheckSql('otps.option_type_price_id > 0', 'otps.price', 'otpd.price');
        $minPriceRound = new \Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $minPriceExpr  = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $minPriceRound);
        $minPriceMin   = new \Zend_Db_Expr("MIN({$minPriceExpr})");
        $minPrice      = $connection->getCheckSql("MIN(o.is_require) = 1", $minPriceMin, '0');
        $maxPriceRound = new \Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $maxPriceExpr  = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $maxPriceRound);
        $maxPrice      = $connection->getCheckSql(
            "(MIN(o.type)='radio' OR MIN(o.type)='drop_down')",
            "MAX({$maxPriceExpr})",
            "SUM({$maxPriceExpr})"
        );

        $select->columns(
            [
                'min_price'   => $minPrice,
                'max_price'   => $maxPrice,
                'customer_id' => 'i.customer_id'
            ]
        );

        $query = $select->insertFromSelect($coaTable);
        $connection->query($query);

        $select = $this->joinCopTableForApplyCustomOption($connection);

        $optPriceType  = $connection->getCheckSql('ops.option_price_id > 0', 'ops.price_type', 'opd.price_type');
        $optPriceValue = $connection->getCheckSql('ops.option_price_id > 0', 'ops.price', 'opd.price');

        $minPriceRound = new \Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $priceExpr     = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $minPriceRound);
        $minPrice      = $connection->getCheckSql("{$priceExpr} > 0 AND o.is_require > 1", $priceExpr, 0);
        $maxPrice      = $priceExpr;

        $select->columns(
            [
                'min_price'   => $minPrice,
                'max_price'   => $maxPrice,
                'customer_id' => 'i.customer_id'
            ]
        );

        $query = $select->insertFromSelect($coaTable);
        $connection->query($query);

        $select = $connection->select()->from(
            [$coaTable],
            [
                'entity_id',
                'website_id',
                'min_price' => 'SUM(min_price)',
                'max_price' => 'SUM(max_price)',
                'customer_id'
            ]
        )->group(
            ['entity_id', 'website_id']
        );
        $query  = $select->insertFromSelect($copTable);
        $connection->query($query);

        $table  = ['i' => $this->_getDefaultFinalPriceTable()];
        $select = $connection->select()->join(
            ['io' => $copTable],
            'i.entity_id = io.entity_id AND i.website_id = io.website_id',
            []
        );
        $select->columns(
            [
                'min_price' => new \Zend_Db_Expr('i.min_price + io.min_price'),
                'max_price' => new \Zend_Db_Expr('i.max_price + io.max_price'),
            ]
        );

        $query = $select->crossUpdateFromSelect($table);
        $connection->query($query);

        $connection->delete($coaTable);
        $connection->delete($copTable);

        return $this;
    }

    /**
     * @param $connection
     * @return mixed
     */
    protected function joinCoaTableForApplyCustomOption($connection)
    {
        $select = $connection->select()->from(
            ['i' => $this->_getDefaultFinalPriceTable()],
            ['entity_id', 'website_id']
        )->join(
            ['cw' => $this->getTable('store_website')],
            'cw.website_id = i.website_id',
            []
        )->join(
            ['csg' => $this->getTable('store_group')],
            'csg.group_id = cw.default_group_id',
            []
        )->join(
            ['cs' => $this->getTable('store')],
            'cs.store_id = csg.default_store_id',
            []
        )->join(
            ['o' => $this->getTable('catalog_product_option')],
            'o.product_id = i.entity_id',
            ['option_id']
        )->join(
            ['ot' => $this->getTable('catalog_product_option_type_value')],
            'ot.option_id = o.option_id',
            []
        )->join(
            ['otpd' => $this->getTable('catalog_product_option_type_price')],
            'otpd.option_type_id = ot.option_type_id AND otpd.store_id = 0',
            []
        )->joinLeft(
            ['otps' => $this->getTable('catalog_product_option_type_price')],
            'otps.option_type_id = otpd.option_type_id AND otpd.store_id = cs.store_id',
            []
        )->group(
            ['i.entity_id', 'i.website_id', 'o.option_id']
        );

        return $select;
    }

    /**
     * @param $connection
     * @return mixed
     */
    protected function joinCopTableForApplyCustomOption($connection)
    {
        $select = $connection->select()->from(
            ['i' => $this->_getDefaultFinalPriceTable()],
            ['entity_id', 'website_id']
        )->join(
            ['cw' => $this->getTable('store_website')],
            'cw.website_id = i.website_id',
            []
        )->join(
            ['csg' => $this->getTable('store_group')],
            'csg.group_id = cw.default_group_id',
            []
        )->join(
            ['cs' => $this->getTable('store')],
            'cs.store_id = csg.default_store_id',
            []
        )->join(
            ['o' => $this->getTable('catalog_product_option')],
            'o.product_id = i.entity_id',
            ['option_id']
        )->join(
            ['opd' => $this->getTable('catalog_product_option_price')],
            'opd.option_id = o.option_id AND opd.store_id = 0',
            []
        )->joinLeft(
            ['ops' => $this->getTable('catalog_product_option_price')],
            'ops.option_id = opd.option_id AND ops.store_id = cs.store_id',
            []
        );

        return $select;
    }

    /**
     * Mode Final Prices index to primary temporary index table
     *
     * @param int[]|null $entityIds
     * @return $this
     */
    protected function _movePriceDataToIndexTable($entityIds = null)
    {
        $columns = [
            'entity_id'    => 'entity_id',
            'website_id'   => 'website_id',
            'tax_class_id' => 'tax_class_id',
            'price'        => 'orig_price',
            'final_price'  => 'price',
            'min_price'    => 'min_price',
            'max_price'    => 'max_price',
            'customer_id'  => 'customer_id'
        ];

        $connection = $this->getConnection();

        $table      = $this->_getDefaultFinalPriceTable();
        $select     = $connection->select()->from($table, $columns);
        $indexTable = $this->_resources->getTableName('mageworx_catalog_product_index_price');

        /* clean old data */
        $connection->delete(
            $indexTable,
            [
                'entity_id = ?' => $entityIds,
            ]
        );

        $query = $select->insertFromSelect(
            $indexTable,
            [],
            false
        );

        $connection->query($query);
        $connection->delete($table);

        return $this;
    }

    /**
     * Retrieve temporary index table name
     *
     * @param string $table
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIdxTable($table = null)
    {
        return $this->tableStrategy->getTableName('mageworx_catalog_product_index_price');
    }

    /**
     * @return bool|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function hasEntity()
    {
        if ($this->hasEntity === null) {
            $reader = $this->getConnection();

            /** @var \Magento\Framework\DB\Select $select */
            $select = $reader->select()->from(
                [$this->getTable('catalog_product_entity')],
                ['count(entity_id)']
            )->where(
                'type_id =?',
                $this->getTypeId()
            );

            $this->hasEntity = (int)$reader->fetchOne($select) > 0;
        }

        return $this->hasEntity;
    }

    /**
     * @param $select
     * @param $attrCode
     * @param $entity
     * @param $store
     * @param null $condition
     * @param bool $required
     * @return \Zend_Db_Expr
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addAttributeToSelectPriceCustomer(
        $select,
        $attrCode,
        $entity,
        $store,
        $condition = null,
        $required = false
    ) {
        $attribute   = $this->_getAttribute($attrCode);
        $attributeId = $attribute->getAttributeId();

        $attributePriceId        = $this->customerPricesResourceModel->getPriceAttributeId();
        $attributeSpecialPriceId = $this->customerPricesResourceModel->getSpecialPriceAttributeId();

        if ($attributeId == $attributePriceId || $attributeId == $attributeSpecialPriceId) {
            $attributeTable =
                $this->_resources->getTableName('mageworx_catalog_product_entity_decimal_customer_prices');
        } else {
            $attributeTable = $attribute->getBackend()->getTable();
        }

        $connection     = $this->getConnection();
        $joinType       = $condition !== null || $required ? 'join' : 'joinLeft';
        $productIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();

        if ($this->isGlobalAttribute($attribute)) {
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
        $this->joinCondition($select, $condition, $expression);

        return $expression;
    }

    /**
     * @param $select
     * @param $condition
     * @param $expression
     */
    protected function joinCondition($select, $condition, $expression)
    {
        /** @var \Magento\Framework\DB\Select $select */
        if ($condition !== null) {
            $select->where("{$expression}{$condition}");
        }

        return $select;
    }

    /**
     * @param $select
     * @param $joinType
     * @param $alias
     * @param $attributeTable
     * @param $productIdField
     * @param $entity
     * @param $attributeId
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
        /** @var \Magento\Framework\DB\Select $select */

        if ($alias == 'ta_price') {
            $select->{$joinType}(
                [$alias => $attributeTable],
                "{$alias}.{$productIdField} = {$entity} AND {$alias}.attribute_id = {$attributeId}" .
                " AND {$alias}.store_id = 0",
                []
            );
        }

        if ($alias == 'ta_special_price') {
            $select->{$joinType}(
                [$alias => $attributeTable],
                "{$alias}.{$productIdField} = {$entity} AND {$alias}.attribute_id = {$attributeId}" .
                " AND {$alias}.store_id = 0 AND ta_price.customer_id = {$alias}.customer_id",
                []
            );
        }

        return $select;
    }

    /**
     * @param $select
     * @param $sAlias
     * @param $store
     * @param $attributeTable
     * @param $productIdField
     * @param $entity
     * @param $attributeId
     * @return \Magento\Framework\DB\Select
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
        /** @var \Magento\Framework\DB\Select $select */
        $select->joinLeft(
            [$sAlias => $attributeTable],
            "{$sAlias}.{$productIdField} = {$entity} AND {$sAlias}.attribute_id = {$attributeId}" .
            " AND {$sAlias}.store_id = {$store}",
            []
        );

        return $select;
    }

    /**
     * Clean all table
     *
     */
    public function cleanAllTableReindex()
    {
        $connection = $this->getConnection();

        $table = $this->_resources->getTableName('mageworx_catalog_product_entity_decimal_customer_prices');
        $connection->delete($table);

        $table = $this->_resources->getTableName('mageworx_catalog_product_index_price');
        $connection->delete($table);

        $connection->delete($this->_getDefaultFinalPriceTable());
        $connection->delete($this->_getCustomOptionAggregateTable());
        $connection->delete($this->_getCustomOptionPriceTable());
    }

    /**
     * in magento 2.1.2 bag with $attribute->isScopeGlobal() and need add check getIsGlobal() == 2
     *
     * @param $attribute
     * @return bool
     */
    protected function isGlobalAttribute($attribute)
    {
        if ($attribute->isScopeGlobal() || $attribute->getIsGlobal() == 2) {
            return true;
        }

        return false;
    }

    /**
     * @param string $typeCode
     * @return $this
     */
    public function setTypeId($typeCode)
    {
        $this->_typeId = $typeCode;

        return $this;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTypeId()
    {
        if ($this->_typeId === null) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('A product type is not defined for the indexer.')
            );
        }

        return $this->_typeId;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setIsComposite($flag)
    {
        $this->isComposite = (bool)$flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsComposite()
    {
        return $this->isComposite;
    }

    /**
     * @return \Magento\Framework\Indexer\Table\StrategyInterface
     */
    public function getTableStrategy()
    {
        return $this->tableStrategy;
    }

    /**
     * @return string
     */
    protected function _getDefaultFinalPriceTable()
    {
        return $this->tableStrategy->getTableName('mageworx_catalog_product_index_price_final');
    }

    /**
     * @return $this
     */
    protected function _prepareDefaultFinalPriceTable()
    {
        $this->getConnection()->delete($this->_getDefaultFinalPriceTable());

        return $this;
    }

    /**
     * @return string
     */
    protected function _getWebsiteDateTable()
    {
        return $this->getTable('catalog_product_index_website');
    }

    /**
     * @param array $entityIds
     * @param array $customerIds
     * @return CustomerPrice
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareFinalPriceDataCustomer($entityIds, $customerIds)
    {
        return $this->prepareFinalPriceDataForTypeCustomer($entityIds, $this->getTypeId(), $customerIds);
    }

}