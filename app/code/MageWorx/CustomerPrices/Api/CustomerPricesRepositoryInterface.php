<?php
/**
 *
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface CustomerPricesRepositoryInterface
{
    /**
     * Save Customer Prices
     *
     * @param \MageWorx\CustomerPrices\Api\Data\CustomerPricesInterface $prices
     * @param bool $saveOptions
     * @return \MageWorx\CustomerPrices\Api\Data\CustomePricesInterface
     */
    public function save(\MageWorx\CustomerPrices\Api\Data\CustomerPricesInterface $prices, $saveOptions = false);

    /**
     * Delete Customer Price
     *
     * @param \MageWorx\CustomerPrices\Api\Data\CustomerPricesInterface $prices
     * @return bool Will returned True if deleted
     */
    public function delete(\MageWorx\CustomerPrices\Api\Data\CustomerPricesInterface $prices);

    /**
     * Retrieve customer prices matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param bool $returnRawObjects
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $returnRawObjects = false);
}
