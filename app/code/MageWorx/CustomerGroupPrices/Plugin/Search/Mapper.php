<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Plugin\Search;

use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use MageWorx\CustomerGroupPrices\Helper\Group as HelperGroup;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Select;

class Mapper
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperGroup
     */
    protected $helperGroup;

    /**
     * @var AppResource
     */
    private $resource;

    /**
     * ApplyCustomerGroupPricesToFilterMapper constructor.
     *
     * @param HelperData $helperData
     * @param HelperGroup $helperGroup
     * @param AppResource $resource
     */
    public function __construct(
        HelperData $helperData,
        HelperGroup $helperGroup,
        AppResource $resource
    ) {
        $this->helperData  = $helperData;
        $this->helperGroup = $helperGroup;
        $this->resource    = $resource;
    }

    /**
     * @param Select $select
     * @param $customerGroupId
     * @return Select
     * @throws \Zend_Db_Select_Exception
     */
    protected function addFilterCustomerGroupId($select, $customerGroupId)
    {
        if ($this->hasPartFrom($select)) {
            $selectFrom = $select->getPart('from');
            if ($this->hasKeyPriceIndex($selectFrom)) {
                $priceIndex = $selectFrom['price_index'];
                if ($this->hasKeyTableName($priceIndex) && $this->hasKeyJoinCondition($priceIndex)) {
                    $condition    = $priceIndex['joinCondition'];
                    $addCondition = $this->resource->getConnection()->quoteInto(
                        'AND price_index.customer_group_id = ?',
                        $customerGroupId
                    );

                    /** @var \Magento\Framework\DB\Select $select */
                    $selectFrom['price_index']['joinCondition'] = $condition . $addCondition;
                    $select->setPart(Select::FROM, $selectFrom);

                    return $select;
                }
            }
        }

        return $select;
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
     * @param $priceIndex
     * @return bool
     */
    protected function hasKeyTableName($priceIndex)
    {
        return (array_key_exists('tableName', $priceIndex)
            && $priceIndex['tableName'] == 'catalog_product_index_price');
    }

    /**
     * @param $priceIndex
     * @return bool
     */
    protected function hasKeyJoinCondition($priceIndex)
    {
        return !empty($priceIndex['joinCondition']);
    }

}

