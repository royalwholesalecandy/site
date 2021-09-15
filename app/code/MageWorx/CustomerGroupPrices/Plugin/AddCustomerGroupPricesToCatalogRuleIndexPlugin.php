<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Plugin;

use Magento\Indexer\Model\Indexer\CollectionFactory;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Product;
use MageWorx\CustomerGroupPrices\Model\Indexer\RuleProductsSelectBuilder as RuleProductsSelect;

class AddCustomerGroupPricesToCatalogRuleIndexPlugin extends IndexBuilder
{
    /**
     * @var CollectionFactory
     */
    protected $indexerCollectionFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var RuleProductsSelect
     */
    protected $ruleProductsSelectBuilder;

    /**
     * @param \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateFormat
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param HelperData $helperData
     * @param RuleProductsSelect $ruleProductsSelectBuilder
     * @param int $batchCount
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Stdlib\DateTime $dateFormat,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        HelperData $helperData,
        RuleProductsSelect $ruleProductsSelectBuilder,
        $batchCount = 1000
    ) {
        $this->helperData                = $helperData;
        $this->ruleProductsSelectBuilder = $ruleProductsSelectBuilder;
        parent::__construct(
            $ruleCollectionFactory,
            $priceCurrency,
            $resource,
            $storeManager,
            $logger,
            $eavConfig,
            $dateFormat,
            $dateTime,
            $productFactory,
            $batchCount
        );
    }

    /**
     * @param IndexBuilder $index
     * @param callable $proceed
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundReindexFull(IndexBuilder $index, callable $proceed)
    {
        try {
            if ($this->helperData->isEnabledGroupPriceInCatalogPriceRule() &&
                version_compare($this->helperData->getModuleVersion('Magento_CatalogRule'), '101.0.0', '<')) {
                $this->doReindexFull();
            } else {
                $index->doReindexFull();
            }
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @param              $websiteId
     * @param Product|null $product
     *
     * @return \Zend_Db_Statement_Interface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getRuleProductsStmt($websiteId, Product $product = null)
    {
        return $this->ruleProductsSelectBuilder->build($websiteId, $product);
    }
}