<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Plugin\Adapter\Mysql\Dynamic;

use MageWorx\CustomerPrices\Helper\Data as HelperData;
use MageWorx\CustomerPrices\Helper\Customer as HelperCustomer;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Model\Session;
use Magento\CatalogSearch\Model\Adapter\Mysql\Dynamic\DataProvider;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface as MysqlDataProviderInterface;
use Magento\Framework\DB\Select;

class ApplyCustomPriceToAggregations
{
    /**
     * @var HelperData
     */
    protected $helperData;

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
     * @var Session
     */
    private $customerSession;

    /**
     * @var MysqlDataProviderInterface
     */
    private $dataProvider;

    /**
     * @var ResourceConnection
     */
    private $connection;

    protected $entityId;
    protected $customerId;

    /**
     * ApplyCustomPriceToAggregations constructor.
     *
     * @param HelperData $helperData
     * @param HelperCustomer $helperCustomer
     * @param HelperCalculate $helperCalculata
     * @param ResourceConnection $resource
     * @param Session $customerSession
     * @param MysqlDataProviderInterface $dataProvider
     * @param ResourceCustomerPrices $customerPricesResourceModel
     */
    public function __construct(
        HelperData $helperData,
        HelperCustomer $helperCustomer,
        HelperCalculate $helperCalculata,
        ResourceConnection $resource,
        Session $customerSession,
        MysqlDataProviderInterface $dataProvider,
        ResourceCustomerPrices $customerPricesResourceModel
    ) {
        $this->helperData                  = $helperData;
        $this->helperCustomer              = $helperCustomer;
        $this->helperCalculate             = $helperCalculata;
        $this->resource                    = $resource;
        $this->customerSession             = $customerSession;
        $this->dataProvider                = $dataProvider;
        $this->customerPricesResourceModel = $customerPricesResourceModel;
        $this->connection                  = $resource->getConnection();
        $this->entityId                    = $this->helperCalculate->getLinkField();
        $this->customerId                  = $this->helperCustomer->getCurrentCustomerId();
    }

    /**
     * @param DataProvider $object
     * @param callable $proceed
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param $range
     * @param \Magento\Framework\Search\Dynamic\EntityStorage $entityStorage
     * @return array
     * @throws \Zend_Db_Select_Exception
     */
    public function aroundGetAggregation(
        DataProvider $object,
        callable $proceed,
        BucketInterface $bucket,
        array $dimensions,
        $range,
        \Magento\Framework\Search\Dynamic\EntityStorage $entityStorage
    ) {
        $select = $this->dataProvider->getDataSet($bucket, $dimensions, $entityStorage->getSource());
        $column = $select->getPart(Select::COLUMNS)[0];

        if ($this->customerId !== null) {
            $tableName = $this->resource->getTableName('mageworx_catalog_product_index_price');
            $select->joinLeft(
                ['mageworx_price_index' => $tableName],
                'mageworx_price_index.entity_id = main_table.entity_id
                AND mageworx_price_index.website_id = main_table.website_id
                AND mageworx_price_index.customer_id = ' . $this->customerId,
                []
            );

            $select->reset(Select::COLUMNS);
            $rangeExpr = new \Zend_Db_Expr(
                $this->connection->getIfNullSql(
                    $this->connection->quoteInto(
                        'FLOOR('.'IFNULL(mageworx_price_index.min_price, main_table.' . $column[1] . ')'.' / ? ) + 1',
                        $range
                    ),
                    1
                )
            );
        } else {
            $select->reset(Select::COLUMNS);
            $rangeExpr = new \Zend_Db_Expr(
                $this->connection->getIfNullSql(
                    $this->connection->quoteInto(
                        'FLOOR(' . $column[1] . ' / ? ) + 1',
                        $range
                    ),
                    1
                )
            );
        }

        $select
            ->columns(['range' => $rangeExpr])
            ->columns(['metrix' => 'COUNT(*)'])
            ->group('range')
            ->order('range');
        $result = $this->connection->fetchPairs($select);

        return $result;
    }

    /**
     * @param DataProvider $object
     * @param callable $proceed
     * @param \Magento\Framework\Search\Dynamic\EntityStorage $entityStorage
     * @return array
     */
    public function aroundGetAggregations(
        \Magento\CatalogSearch\Model\Adapter\Mysql\Dynamic\DataProvider $object,
        callable $proceed,
        \Magento\Framework\Search\Dynamic\EntityStorage $entityStorage
    ) {
        if ($this->customerId !== null) {
            $aggregation = [
                'count' => 'count(DISTINCT main_table.entity_id)',
                'max'   => 'MAX(IFNULL(mageworx_price_index.min_price, main_table.min_price))',
                'min'   => 'MIN(IFNULL(mageworx_price_index.min_price, main_table.min_price))',
                'std'   => 'STDDEV_SAMP(IFNULL(mageworx_price_index.min_price, main_table.min_price))',
            ];
        } else {
            $aggregation = [
                'count' => 'count(DISTINCT main_table.entity_id)',
                'max'   => 'MAX(min_price)',
                'min'   => 'MIN(min_price)',
                'std'   => 'STDDEV_SAMP(min_price)',
            ];
        }

        $select = $this->getSelect();

        $tableName = $this->resource->getTableName('catalog_product_index_price');
        /** @var Table $table */
        $table = $entityStorage->getSource();
        $select->from(['main_table' => $tableName], [])
               ->joinInner(
                   ['entities' => $table->getName()],
                   'main_table.entity_id  = entities.entity_id',
                   []
               );

        $select = $this->setCustomerGroupId($select);

        if ($this->customerId != null) {
            $indexTableName = $this->resource->getTableName('mageworx_catalog_product_index_price');
            $select->joinLeft(
                ['mageworx_price_index' => $indexTableName],
                'mageworx_price_index.entity_id = main_table.entity_id
                AND mageworx_price_index.website_id = main_table.website_id
                AND mageworx_price_index.customer_id = ' . $this->customerId,
                []
            );
        }

        $select->columns($aggregation);
        $result = $this->connection->fetchRow($select);

        return $result;
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }

    /**
     * @param $select
     * @return mixed
     */
    private function setCustomerGroupId($select)
    {
        return $select->where('customer_group_id = ?', $this->customerSession->getCustomerGroupId());
    }

}