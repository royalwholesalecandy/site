<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Plugin;

use Magento\Indexer\Model\Indexer\CollectionFactory;
use MageWorx\CustomerPrices\Helper\Data as HelperData;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Product;
use MageWorx\CustomerPrices\Model\Indexer\RuleProductsSelectBuilder as RuleProductsSelect;

class AddCustomerPricesToCatalogRuleIndexPlugin extends IndexBuilder
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
     * @param $result
     * @return mixed
     * @throws \Exception
     */
    public function afterReindexFull(IndexBuilder $index, $result)
    {
        if ($this->helperData->isEnabledCustomerPriceInCatalogPriceRule()) {
            $this->deleteOldData();
            $this->applyAllRules();
        }

        return $result;
    }

    /**
     * @param Product|null $product
     * @return $this|void
     * @throws \Zend_Db_Statement_Exception
     */
    protected function applyAllRules(Product $product = null)
    {
        $fromDate = mktime(0, 0, 0, date('m'), date('d') - 1);
        $toDate   = mktime(0, 0, 0, date('m'), date('d') + 1);

        foreach ($this->storeManager->getWebsites() as $website) {
            $productsStmt = $this->ruleProductsSelectBuilder->build($website->getId(), $product);
            $dayPrices    = [];
            $stopFlags    = [];
            $prevKey      = null;

            while ($ruleData = $productsStmt->fetch()) {
                $ruleProductId = $ruleData['product_id'];
                $productKey    = $this->createProductKey($ruleData, $ruleProductId);

                if ($prevKey && $prevKey != $productKey) {
                    $stopFlags = [];
                    if (count($dayPrices) > $this->batchCount) {
                        $this->saveRuleProductPrices($dayPrices);
                        $dayPrices = [];
                    }
                }

                $ruleData['from_time'] = $this->roundTime($ruleData['from_time']);
                $ruleData['to_time']   = $this->roundTime($ruleData['to_time']);
                /* Build prices for each day */
                $dayPrices = $this->buildPricesForEachDay(
                    $productKey,
                    $ruleProductId,
                    $fromDate,
                    $toDate,
                    $ruleData,
                    $dayPrices,
                    $stopFlags
                );

                $prevKey = $productKey;
            }
            $this->saveRuleProductPrices($dayPrices);
        }
    }

    /**
     * @param $ruleData
     * @param $ruleProductId
     * @return string
     */
    protected function createProductKey($ruleData, $ruleProductId)
    {
        $productKey = $ruleProductId .
            '_' .
            $ruleData['website_id'] .
            '_' .
            $ruleData['customer_group_id'] .
            '_' .
            $ruleData['customer_id'];

        return $productKey;
    }

    /**
     * @param $productKey
     * @param $ruleProductId
     * @param $fromDate
     * @param $toDate
     * @param $ruleData
     * @param $dayPrices
     * @param $stopFlags
     * @return mixed
     */
    protected function buildPricesForEachDay(
        $productKey,
        $ruleProductId,
        $fromDate,
        $toDate,
        $ruleData,
        $dayPrices,
        $stopFlags
    ) {
        for ($time = $fromDate; $time <= $toDate; $time += self::SECONDS_IN_DAY) {
            if (($ruleData['from_time'] == 0 ||
                    $time >= $ruleData['from_time']) && ($ruleData['to_time'] == 0 ||
                    $time <= $ruleData['to_time'])
            ) {
                $priceKey = $time . '_' . $productKey . '_' . $ruleData['customer_id'];

                if (isset($stopFlags[$priceKey])) {
                    continue;
                }

                if (!isset($dayPrices[$priceKey])) {
                    $dayPrices[$priceKey] = [
                        'rule_date'         => $time,
                        'website_id'        => $ruleData['website_id'],
                        'customer_group_id' => $ruleData['customer_group_id'],
                        'product_id'        => $ruleProductId,
                        'rule_price'        => $this->calcRuleProductPrice($ruleData),
                        'latest_start_date' => $ruleData['from_time'],
                        'earliest_end_date' => $ruleData['to_time'],
                        'customer_id'       => $ruleData['customer_id']
                    ];
                } else {
                    $dayPrices[$priceKey]['rule_price']        = $this->calcRuleProductPrice(
                        $ruleData,
                        $dayPrices[$priceKey]
                    );
                    $dayPrices[$priceKey]['latest_start_date'] = max(
                        $dayPrices[$priceKey]['latest_start_date'],
                        $ruleData['from_time']
                    );
                    $dayPrices[$priceKey]['earliest_end_date'] = min(
                        $dayPrices[$priceKey]['earliest_end_date'],
                        $ruleData['to_time']
                    );
                }

                if ($ruleData['action_stop']) {
                    $stopFlags[$priceKey] = true;
                }
            }
        }

        return $dayPrices;
    }


    /**
     * @param array $arrData
     * @return $this
     * @throws \Exception
     */
    protected function saveRuleProductPrices($arrData)
    {
        if (empty($arrData)) {
            return $this;
        }

        $productIds = [];

        try {
            foreach ($arrData as $key => $data) {
                $productIds['product_id']           = $data['product_id'];
                $arrData[$key]['rule_date']         = $this->dateFormat->formatDate($data['rule_date'], false);
                $arrData[$key]['latest_start_date'] = $this->dateFormat->formatDate($data['latest_start_date'], false);
                $arrData[$key]['earliest_end_date'] = $this->dateFormat->formatDate($data['earliest_end_date'], false);
            }
            
            $this->connection->insertOnDuplicate($this->getTable('mageworx_catalogrule_product_price'), $arrData);

        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteOldData()
    {
        $this->connection->delete($this->getTable('mageworx_catalogrule_product_price'));

        return $this;
    }

    /**
     * @param int $timeStamp
     * @return int
     */
    private function roundTime($timeStamp)
    {
        if (is_numeric($timeStamp) && $timeStamp != 0) {
            $timeStamp = $this->dateTime->timestamp($this->dateTime->date('Y-m-d 00:00:00', $timeStamp));
        }

        return $timeStamp;
    }

}