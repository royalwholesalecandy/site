<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Plugin\Search;

use Magento\CatalogSearch\Model\Search\TableMapper;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use MageWorx\CustomerGroupPrices\Helper\Group as HelperGroup;
use Magento\Framework\App\ResourceConnection as AppResource;

class ApplyCustomerGroupPricesToTableMapper extends Mapper
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
     * ApplyCustomerGroupPricesToTableMapper constructor.
     *
     * @param HelperData $helperData
     * @param HelperGroup $helperGroup
     * @param AppResource $resource
     *
     */
    public function __construct(
        HelperData $helperData,
        HelperGroup $helperGroup,
        AppResource $resource
    ) {
        $this->helperData  = $helperData;
        $this->helperGroup = $helperGroup;
        $this->resource    = $resource;
        parent::__construct($helperData, $helperGroup, $resource);
    }

    /**
     * This plugin will only work with Magento versions <= 2.1.7
     * Change temp search_tmp_******** table creation
     *
     * join mageworx_catalog_product_index_price to subquery which is used in temp table search_tmp_******** creation
     * add filer 'AND price_index.customer_group_id = ?' to
     * LEFT JOIN `catalog_product_index_price` AS `price_index` ON search_index.entity_id = price_index.entity_id
     *                                                             AND price_index.website_id = '1'
     *
     * @param TableMapper $subject
     * @param $result
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Select_Exception
     */
    public function afterAddTables(TableMapper $subject, $result)
    {
        $customerGroupId = $this->helperGroup->getCurrentCustomerGroupId();
        if (!$this->helperData->isEnabledCustomerGroupPrice() || $customerGroupId === null) {
            return $result;
        }
        $this->addFilterCustomerGroupId($result, $customerGroupId);

        return $result;
    }
}