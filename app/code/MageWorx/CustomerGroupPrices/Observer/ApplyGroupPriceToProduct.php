<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices;
use MageWorx\CustomerGroupPrices\Model\Config\Source\GroupCustomers;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use MageWorx\CustomerGroupPrices\Helper\Group as HelperGroup;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\DataObject;

class ApplyGroupPriceToProduct implements ObserverInterface
{
    /**
     * @var CustomerGroupPrices
     */
    protected $customerGroupPricesResourceModel;

    /**
     * @var GroupCustomers
     */
    protected $configGroupCustomers;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperGroup
     */
    protected $helperGroup;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var array
     */
    protected $productIds;

    /**
     * ApplyGroupPriceToProduct constructor.
     *
     * @param CustomerGroupPrices $customerGroupPricesResourceModel
     * @param HelperData $helperData
     * @param HelperGroup $helperGroup
     * @param StoreManagerInterface $storeManager
     * @param EventManager $eventManager
     */
    public function __construct(
        CustomerGroupPrices $customerGroupPricesResourceModel,
        HelperData $helperData,
        HelperGroup $helperGroup,
        StoreManagerInterface $storeManager,
        EventManager $eventManager
    ) {
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
        $this->helperData                       = $helperData;
        $this->helperGroup                      = $helperGroup;
        $this->storeManager                     = $storeManager;
        $this->eventManager                     = $eventManager;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->helperData->isEnabledCustomerGroupPrice()) {
            return $this;
        }

        $product = $observer->getData('product');
        if (!$product instanceof Product) {
            return $this;
        }

        $data = new DataObject();
        $data->setProductIds([]);
        $this->eventManager->dispatch('mageworx_customerprices_product_ids', ['object' => $data]);
        $this->productIds = $data->getProductIds();

        if (empty($product['is_calculate_group_price']) || $product['is_calculate_group_price'] == false) {
            /* compatibility with mageworx_customerprices */
            if (!empty($this->productIds)) {
                if (in_array($product->getId(), $this->productIds)) {
                    return $this;
                }
            }
            $productPrice = $this->getProductPrice($product);
            if ($productPrice !== null) {
                $product['price'] = $productPrice;
            }

            if (!empty($product['special_price'])) {
                $specialPrice = $this->getSpecialPrice($product);
                if ($specialPrice !== null) {
                    $product['special_price'] = $specialPrice;
                }
            }

            $product['is_calculate_group_price'] = true;
        }

        return $this;
    }

    /**
     * @param $product
     *
     * @return float|mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductPrice($product)
    {
        $productPrice = null;
        if ($this->helperData->isEnabledCustomerGroupPrice()) {
            $groupCustomer = $this->helperGroup->getCurrentCustomerGroupId();

            /* group price */
            if ($groupCustomer !== null) {
                $groupPriceInfo = $this->customerGroupPricesResourceModel->getGroupPrice($groupCustomer);
                if (!empty($groupPriceInfo) && !empty($groupPriceInfo['price'])) {
                    $productPrice = $this->getCalculatedGroupPrice(
                        $product->getData('price'),
                        $groupPriceInfo['price'],
                        $groupPriceInfo['price_type']
                    );
                }
            }
            /* get product group price  */
            $newProductPrice = $this->getProductGroupPrice($groupCustomer, $product);
            if ($newProductPrice !== null) {
                $productPrice = $newProductPrice;
            }
        }

        return $productPrice;
    }

    /**
     * @param $groupCustomer
     * @param $product
     *
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductGroupPrice($groupCustomer, $product)
    {
        $websiteId        = $this->storeManager->getWebsite()->getData('website_id');
        $productGroupData = $this->customerGroupPricesResourceModel->getGroupPricesProduct($product->getId(), 0);

        if (!empty($productGroupData)) {
            foreach ($productGroupData as $value) {
                if ($value['is_all_groups'] == 1) {
                    return $this->getCalculatedProductGroupPrice(
                        $product->getData('price'),
                        $value['price'],
                        $value['price_type']
                    );
                }
                if (($value['website_id'] == $websiteId || $value['website_id'] == HelperData::ALL_WEBSITE)
                    && $value['group_id'] == $groupCustomer) {
                    return $this->getCalculatedProductGroupPrice(
                        $product->getData('price'),
                        $value['price'],
                        $value['price_type']
                    );
                }
            }
        }

        return null;
    }

    /**
     * @param $product
     *
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getSpecialPrice($product)
    {
        if ($this->helperData->isEnabledCustomerGroupPrice()) {
            $groupCustomer = $this->helperGroup->getCurrentCustomerGroupId();

            return $this->getProductGroupSpecialPrice($groupCustomer, $product);
        }

        return null;
    }

    /**
     * @param $groupCustomer
     * @param $product
     *
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductGroupSpecialPrice($groupCustomer, $product)
    {
        $websiteId        = $this->storeManager->getWebsite()->getData('website_id');
        $productGroupData = $this->customerGroupPricesResourceModel->getGroupPricesProduct($product->getId(), 1);
        $productPrice     = $product->getData('special_price');
        if (!empty($productGroupData)) {
            foreach ($productGroupData as $value) {
                if ($value['is_all_groups'] == 1) {
                    return $this->getCalculatedProductGroupPrice($productPrice, $value['price'], $value['price_type']);
                }
                if (($value['website_id'] == $websiteId || $value['website_id'] == HelperData::ALL_WEBSITE)
                    && $value['group_id'] == $groupCustomer) {
                    return $this->getCalculatedProductGroupPrice($productPrice, $value['price'], $value['price_type']);
                }
            }
        }

        return null;
    }

    /**
     *
     * @param $priceProduct
     * @param $customPrice
     * @param $type
     *
     * @return float
     */
    protected function getCalculatedGroupPrice($priceProduct, $customPrice, $type)
    {
        /* $type == 0 - fixed price */
        if ($type == 0) {
            $priceProduct = $priceProduct + (float)$customPrice;

            return $priceProduct;
        } else {
            $percent = 1 + (float)$customPrice / 100;

            return $percent * $priceProduct;
        }
    }

    /**
     * Get calculated product group price
     *
     * @param $priceProduct
     * @param $customPrice
     * @param $type
     *
     * @return mixed
     */
    protected function getCalculatedProductGroupPrice($priceProduct, $customPrice, $type)
    {
        // fixed price
        if ($type == 0) {
            if ($this->isFindMathSymbolPlus($customPrice) || $this->isFindMathSymbolMinus($customPrice)) {
                return $priceProduct + $customPrice;
            }

            return $customPrice;
        }

        if ($this->isFindMathSymbolPlus($customPrice) || $this->isFindMathSymbolMinus($customPrice)) {
            return $priceProduct + $priceProduct * ($customPrice / 100);
        }

        return $priceProduct * ($customPrice / 100);

    }

    /**
     * @param $customPrice
     *
     * @return bool
     */
    protected function isFindMathSymbolPlus($customPrice)
    {
        if (strripos($customPrice, '+') === false) {
            return false;
        }

        return true;
    }

    /**
     * @param $customPrice
     *
     * @return bool
     */
    protected function isFindMathSymbolMinus($customPrice)
    {
        if (strripos($customPrice, '-') === false) {
            return false;
        }

        return true;
    }
}