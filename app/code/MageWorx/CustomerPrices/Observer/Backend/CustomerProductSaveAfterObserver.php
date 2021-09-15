<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use \MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices;
use MageWorx\CustomerPrices\Helper\Data as HelperData;
use MageWorx\CustomerPrices\Model\CustomerPrices as CustomerPricesModel;
use MageWorx\CustomerPrices\Model\ResourceModel\Product\Indexer\CustomerPrice as IndexCustomPrice;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;

class CustomerProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var CustomerPrices
     */
    private $customerPriceResourceModel;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var \MageWorx\CustomerPrices\Helper\Calculate
     */
    private $helperCalculate;

    /**
     * @var IndexCustomPrice
     */
    private $indexer;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * CustomerProductSaveAfterObserver constructor.
     *
     * @param CustomerPrices $customerPriceResourceModel
     * @param HelperData $helperData
     * @param \MageWorx\CustomerPrices\Helper\Calculate $helperCalculate
     * @param IndexCustomPrice $indexer
     * @param ProductRepositoryInterface $productRepository
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        CustomerPrices $customerPriceResourceModel,
        HelperData $helperData,
        \MageWorx\CustomerPrices\Helper\Calculate $helperCalculate,
        IndexCustomPrice $indexer,
        ProductRepositoryInterface $productRepository,
        IndexerRegistry $indexerRegistry
    ) {
        $this->customerPriceResourceModel = $customerPriceResourceModel;
        $this->helperData                 = $helperData;
        $this->helperCalculate            = $helperCalculate;
        $this->indexer                    = $indexer;
        $this->productRepository          = $productRepository;
        $this->indexerRegistry            = $indexerRegistry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $prepareDataToSaveCustomerPrices = [];
        $prepareDataToReindex            = [];
        $customerId                      = null;
        $request                         = $observer->getEvent()->getRequest();
        $productPriceData                = $request->getParam('select_products_price');

        if (is_null($productPriceData)) {
            return $this;
        }

        $productPriceData = json_decode($productPriceData);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Unable to unserialize value.');

            return $this;
        }

        $customerDataFromRequest = $request->getParam('customer', []);

        if (!empty($customerDataFromRequest['entity_id'])) {
            $customerId = $customerDataFromRequest['entity_id'];
        }

        if (!is_null($customerId) && !$this->isNeedSaveCustomerPrices($customerId, $productPriceData)) {
            return $this;
        }

        foreach ($productPriceData as $productId => $priceData) {
            if (empty($productId) || (empty($priceData->price) && empty($priceData->special_price))) {
                continue;
            }

            try {
                $product = $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                continue;
            }

            $price            = $priceData->price;
            $specialPrice     = $priceData->special_price;
            $priceType        = $this->helperCalculate->getPriceType($price);
            $specialPriceType = $this->helperCalculate->getPriceType($specialPrice);

            $priceSign        = $this->helperCalculate->getPriceSign($price);
            $specialPriceSign = $this->helperCalculate->getPriceSign($specialPrice);

            $priceValue        = $this->getAbsPriceValue($price);
            $specialPriceValue = $this->getAbsPriceValue($specialPrice);

            /* prepare data to save */
            $prepareDataToSaveCustomerPrices[] = [
                'attribute_type'      => CustomerPricesModel::TYPE_CUSTOMER,
                'customer_id'         => $customerId,
                'product_id'          => $productId,
                'price'               => $price,
                'price_type'          => $priceType,
                'special_price'       => $specialPrice,
                'special_price_type'  => $specialPriceType,
                'discount'            => null,
                'discount_price_type' => 1,
                'price_sign'          => $priceSign,
                'price_value'         => $priceValue,
                'special_price_sign'  => $specialPriceSign,
                'special_price_value' => $specialPriceValue
            ];

            $prepareDataToReindex[] = [
                'product_id'   => $productId,
                'customer_id'  => $customerId,
                'product_type' => $product->getTypeId()
            ];
        }

        /* delete old data */
        if (!is_null($customerId)) {
            $this->customerPriceResourceModel->deleteProductsByCustomer([$customerId]);
        }

        if (!empty($prepareDataToSaveCustomerPrices)) {
            /* save new data */
            $this->customerPriceResourceModel->saveCustomerProductsPrices($prepareDataToSaveCustomerPrices);
        }

        /* reindex data */
        foreach ($prepareDataToReindex as $reindexData) {
            $this->indexer->setTypeId($reindexData['product_type']);
            $this->indexer->reindexEntityCustomer([$reindexData['product_id']], [$reindexData['customer_id']]);
        }

        /* add notification need reindex catalogrule_rule */
        if ($this->helperData->isEnabledCustomerPriceInCatalogPriceRule()) {
            $this->indexerRegistry->get(RuleProductProcessor::INDEXER_ID)->invalidate();
        }

        return $this;
    }

    /**
     * Srt to float and abs
     *
     * @param $strPrice
     * @return float|int|null
     */
    protected function getAbsPriceValue($strPrice)
    {
        if ($strPrice == '') {
            return null;
        }

        return abs(floatval($strPrice));
    }

    /**
     * @param int $customerId
     * @param array $productPriceData
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isNeedSaveCustomerPrices($customerId, $productPriceData)
    {
        $allCustomerData = $this->customerPriceResourceModel->getFullCustomersPricesData([$customerId]);

        if (count($productPriceData) != count($allCustomerData)) {
            return true;
        }

        foreach ($productPriceData as $productId => $priceData) {
            if (empty($productId) || (empty($priceData->price) && empty($priceData->special_price))) {
                continue;
            }

            $hasCustomerPriceRow = $this->hasRowCustomerPriceCollection(
                $allCustomerData,
                $productId,
                $priceData->price,
                $priceData->special_price
            );

            if (!$hasCustomerPriceRow) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $allCustomerCollection
     * @param int $productId
     * @param string $price
     * @param string $specialPrice
     * @return bool
     */
    protected function hasRowCustomerPriceCollection($allCustomerCollection, $productId, $price, $specialPrice)
    {
        foreach ($allCustomerCollection as $value) {
            if ($value['product_id'] == $productId
                && $value['price'] == $price
                && $value['special_price'] == $specialPrice) {
                return true;
            }
        }

        return false;
    }
}