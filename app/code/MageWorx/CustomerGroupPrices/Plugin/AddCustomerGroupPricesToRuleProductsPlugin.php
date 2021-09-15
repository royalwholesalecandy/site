<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Plugin;

use MageWorx\CustomerGroupPrices\Model\Indexer\RuleProductsSelectBuilder as RuleProductsSelect;
use Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;

class AddCustomerGroupPricesToRuleProductsPlugin
{
    /**
     * @var RuleProductsSelect
     */
    protected $indexerRuleProductsSelectBuilder;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * AddCustomerGroupPricesToRuleProductsPlugin constructor.
     *
     * @param RuleProductsSelect $indexerRuleProductsSelectBuilder
     * @param HelperData $helperData
     */
    public function __construct(
        RuleProductsSelect $indexerRuleProductsSelectBuilder,
        HelperData $helperData
    ) {
        $this->indexerRuleProductsSelectBuilder = $indexerRuleProductsSelectBuilder;
        $this->helperData                       = $helperData;
    }

    /**
     * @param RuleProductsSelectBuilder $index
     * @param callable $proceed
     * @param                                     $websiteId
     * @param \Magento\Catalog\Model\Product|null $product
     * @param                                     $useAdditionalTable
     *
     * @return \Zend_Db_Statement_Interface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundBuild(
        RuleProductsSelectBuilder $index,
        callable $proceed,
        $websiteId,
        \Magento\Catalog\Model\Product $product = null,
        $useAdditionalTable
    ) {
        return $this->indexerRuleProductsSelectBuilder->build($websiteId, $product, $useAdditionalTable);
    }
}