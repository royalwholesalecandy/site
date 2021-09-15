<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Plugin\Search;

use MageWorx\CustomerPrices\Helper\Data as HelperData;
use MageWorx\CustomerPrices\Helper\Customer as HelperCustomer;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\DB\Select;

class Mapper
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
     * @var AppResource
     */
    private $resource;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * ApplyCustomerPricesToTableMapper constructor.
     *
     * @param HelperData $helperData
     * @param HelperCustomer $helperCustomer
     * @param HelperCalculate $helperCalculate
     * @param AppResource $resource
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        HelperData $helperData,
        HelperCustomer $helperCustomer,
        HelperCalculate $helperCalculate,
        AppResource $resource,
        ModuleManager $moduleManager
    ) {
        $this->helperData      = $helperData;
        $this->helperCustomer  = $helperCustomer;
        $this->helperCalculate = $helperCalculate;
        $this->resource        = $resource;
        $this->moduleManager   = $moduleManager;
    }

    /**
     * @param Select $select
     * @param int $customerGroupId
     * @param int $customerId
     * @param int $websiteId
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Select_Exception
     */
    protected function addFilterCustomerGroupId($select, $customerGroupId, $customerId, $websiteId)
    {
        if (!$this->isCorrectId($customerGroupId, $customerId, $websiteId)) {
            return $select;
        }

        if ($this->moduleManager->isOutputEnabled('MageWorx_CustomerGroupPrices')) {
            return $select;
        }

        if (!$this->hasPartFrom($select)) {
            return $select;
        }

        $selectFrom = $select->getPart('from');
        if (!$this->hasKeyPriceIndex($selectFrom)) {
            return $select;
        }

        $priceIndex = $selectFrom['price_index'];
        if ($this->hasKeyTableName($priceIndex) && $this->hasKeyJoinCondition($priceIndex)) {
            $condition    = $priceIndex['joinCondition'];
            $addCondition = $this->resource->getConnection()->quoteInto(
                'AND price_index.customer_group_id = ?',
                $customerGroupId
            );

            /** @var \Magento\Framework\DB\Select $select */
            $selectFrom['price_index']['joinCondition'] = $condition . $addCondition;
            $select->setPart(\Magento\Framework\DB\Select::FROM, $selectFrom);

            return $select;
        }
    }

    /**
     * @param $select
     * @return bool
     */
    protected function hasPartFrom($select)
    {
        return (is_array($select->getPart('from')) && !empty($select->getPart('from')));
    }

    /**
     * @param array $selectFrom
     * @return bool
     */
    protected function hasKeyPriceIndex($selectFrom)
    {
        return !empty($selectFrom['price_index']);
    }

    /**
     * @param array $priceIndex
     * @return bool
     */
    protected function hasKeyTableName($priceIndex)
    {
        return (array_key_exists('tableName', $priceIndex)
            && $priceIndex['tableName'] == 'catalog_product_index_price');
    }

    /**
     * @param array $priceIndex
     * @return bool
     */
    protected function hasKeyJoinCondition($priceIndex)
    {
        return !empty($priceIndex['joinCondition']);
    }

    /**
     * @param Select $select
     * @param int $websiteId
     * @param int $customerId
     * @return Select
     */
    protected function joinMageworxPriceIndex($select, $websiteId, $customerId)
    {
        $tableName = $this->resource->getTableName('mageworx_catalog_product_index_price');
        /** @var Select $select */
        $select->joinLeft(
            ['mageworx_price_index' => $tableName],
            'search_index.entity_id = mageworx_price_index.entity_id
                       AND mageworx_price_index.website_id = ' . $websiteId . '
                       AND mageworx_price_index.customer_id = ' . $customerId,
            []
        );

        return $select;
    }

    /**
     * @param int $customerGroupId
     * @param int $customerId
     * @param int $websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function isCorrectId($customerGroupId, $customerId, $websiteId)
    {
        if ($customerGroupId === null || $customerId === null || $websiteId === null) {
            return false;
        }

        return true;
    }
}