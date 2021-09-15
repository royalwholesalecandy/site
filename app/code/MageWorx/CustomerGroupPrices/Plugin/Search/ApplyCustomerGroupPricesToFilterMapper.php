<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Plugin\Search;

use Magento\CatalogSearch\Model\Search\FilterMapper\ExclusionStrategy;
use Magento\Framework\DB\Select;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use MageWorx\CustomerGroupPrices\Helper\Group as HelperGroup;
use Magento\Framework\App\ResourceConnection as AppResource;

class ApplyCustomerGroupPricesToFilterMapper extends Mapper
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
        parent::__construct($helperData, $helperGroup, $resource);
    }

    /**
     *
     * This plugin will only work with Magento versions >= 2.1.8
     * Change temp search_tmp_******** table creation
     *
     * join mageworx_catalog_product_index_price to subquery which is used in temp table search_tmp_******** creation
     * add filer 'AND price_index.customer_group_id = ?' to
     * LEFT JOIN `catalog_product_index_price` AS `price_index` ON search_index.entity_id = price_index.entity_id
     *                                                             AND price_index.website_id = '1'
     *
     * @param ExclusionStrategy $subject
     * @param callable $proceed
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @param \Magento\Framework\DB\Select $select
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Select_Exception
     */
    public function aroundApply(ExclusionStrategy $subject, callable $proceed, $filter, $select)
    {
        $customerGroupId = $this->helperGroup->getCurrentCustomerGroupId();
        if (!$this->helperData->isEnabledCustomerGroupPrice() || $customerGroupId === null) {
            return $proceed($filter, $select);
        }

        $this->addFilterCustomerGroupId($select, $customerGroupId);

        return $proceed($filter, $select);
    }
}