<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Plugin\Search;

use Magento\CatalogSearch\Model\Search\FilterMapper\ExclusionStrategy;
use MageWorx\CustomerPrices\Helper\Data as HelperData;
use MageWorx\CustomerPrices\Helper\Customer as HelperCustomer;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\Module\Manager as ModuleManager;

class ApplyCustomerPricesToFilterMapper extends Mapper
{
    /**
     * @var HelperCustomer
     */
    protected $helperCustomer;

    /**
     * ApplyCustomerPricesToFilterMapper constructor.
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
        $this->helperCustomer = $helperCustomer;

        parent::__construct($helperData, $helperCustomer, $helperCalculate, $resource, $moduleManager);
    }

    /**
     *
     * This plugin will work before Magento > 2.1.7
     * Change create temp table search_tmp_********
     *
     * join mageworx_catalog_product_index_price in subquery which use in create temp table search_tmp_********
     * AND add AND price_index.customer_group_id = ? in
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
    public function beforeApply(ExclusionStrategy $subject, $filter, $select)
    {
        $customerGroupId = $this->helperCustomer->getCurrentCustomerGroupId();
        $customerId      = $this->helperCustomer->getCurrentCustomerId();
        $websiteId       = $this->helperCustomer->getWebsiteId();


        if ($this->isCorrectId($customerGroupId, $customerId, $websiteId)
            && $filter->getField() === 'price') {
            $this->addFilterCustomerGroupId($select, $customerGroupId, $customerId, $websiteId);
            $this->joinMageworxPriceIndex($select, $websiteId, $customerId);
        }

        return [
            $filter,
            $select
        ];
    }
}