<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Observer\Adminhtml;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use MageWorx\CustomerGroupPrices\Helper\Group as HelperGroup;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;

class AddGroupPriceDataToProduct implements ObserverInterface
{
    const CUST_GROUP_ALL = \Magento\Customer\Model\GroupManagement::CUST_GROUP_ALL;

    /**
     * @var CustomerGroupPrices
     */
    protected $customerGroupPricesResourceModel;

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
     * AddGroupPriceDataToProduct constructor.
     *
     * @param CustomerGroupPrices $customerGroupPricesResourceModel
     * @param HelperData $helperData
     * @param HelperGroup $helperGroup
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerGroupPrices $customerGroupPricesResourceModel,
        HelperData $helperData,
        HelperGroup $helperGroup,
        StoreManagerInterface $storeManager
    ) {
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
        $this->helperData                       = $helperData;
        $this->helperGroup                      = $helperGroup;
        $this->storeManager                     = $storeManager;
    }

    /**
     * @param EventObserver $observer
     *
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

        $groupPrices        = $this->customerGroupPricesResourceModel->getGroupPricesProduct($product->getId(), 0);
        $specialGroupPrices = $this->customerGroupPricesResourceModel->getGroupPricesProduct($product->getId(), 1);

        if (!empty($groupPrices)) {
            foreach ($groupPrices as $groupPrice) {
                if (!empty($groupPrice['is_manual']) && $groupPrice['is_manual'] == 1) {
                    $dateGroupPrice[] = [
                        'cust_group'       => $this->getCustomerGroup($groupPrice),
                        'is_all_groups'    => !empty($groupPrice['is_all_groups']) ? $groupPrice['is_all_groups'] : 0,
                        'website_id'       => !empty($groupPrice['website_id']) ? $groupPrice['website_id'] : 0,
                        'group_price'      => !empty($groupPrice['price']) ? $groupPrice['price'] : 0,
                        'group_type_price' => !empty($groupPrice['price_type']) ? $groupPrice['price_type'] : 0
                    ];
                }
            }
            $product['mageworx_group_price'] = $dateGroupPrice;
            unset($dateGroupPrice);
        }

        if (!empty($specialGroupPrices)) {
            $dateSpecialGroupPrice = [];
            foreach ($specialGroupPrices as $specialGroupPrice) {
                if (!empty($specialGroupPrice['is_manual']) && $specialGroupPrice['is_manual'] == 1) {
                    $dateSpecialGroupPrice[] = [
                        'cust_group'       => $this->getCustomerGroup($specialGroupPrice),
                        'is_all_groups'    => !empty($specialGroupPrice['is_all_groups']) ? $specialGroupPrice['is_all_groups'] : 0,
                        'website_id'       => !empty($specialGroupPrice['website_id']) ? $specialGroupPrice['website_id'] : 0,
                        'group_price'      => !empty($specialGroupPrice['price']) ? $specialGroupPrice['price'] : 0,
                        'group_type_price' => !empty($specialGroupPrice['price_type']) ? $specialGroupPrice['price_type'] : 0
                    ];
                }
            }
            $product['mageworx_special_group_price'] = $dateSpecialGroupPrice;
            unset($dateSpecialGroupPrice);
        }

        return $this;
    }

    /**
     * @param array $groupPrice
     *
     * @return int
     */
    protected function getCustomerGroup($groupPrice)
    {
        if (!empty($groupPrice['group_id'])) {
            return $groupPrice['group_id'];
        }
        if (!empty($groupPrice['is_all_groups']) && $groupPrice['is_all_groups'] == 1) {
            return $customerGroup = self::CUST_GROUP_ALL;
        }

        return 0;
    }

    /**
     * @param $product
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getSpecialPrice($product)
    {
        $productPrice = empty($product->getData('special_price')) ? $product->getData('price') : '';

        if ($this->helperData->isEnabledCustomerGroupPrice()) {
            $groupCustomer = $this->helperGroup->getCurrentCustomerGroupId();
            /* get group price product */
            $productPrice = $this->getProductGroupSpecialPrice($productPrice, $groupCustomer, $product);
        }

        return $productPrice;
    }

    /**
     * @param $product
     *
     * @return float|int|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getPrice($product)
    {
        $productPrice = $product->getData('price');
        if ($this->helperData->isEnabledCustomerGroupPrice()) {
            $groupCustomer = $this->helperGroup->getCurrentCustomerGroupId();

            if ($groupCustomer !== 0) {
                $groupPriceInfo = $this->customerGroupPricesResourceModel->getGroupPrice($groupCustomer);
                if (!empty($groupPriceInfo) && !empty($groupPriceInfo['price'])) {
                    $productPrice = $this->getGroupPrice(
                        $productPrice,
                        $groupPriceInfo['price'],
                        $groupPriceInfo['price_type']
                    );
                }
            }

            /* get group price product */
            $productPrice = $this->getProductGroupPrice($productPrice, $groupCustomer, $product);
        }

        return $productPrice;
    }

    /**
     * @param $productPrice
     * @param $groupCustomer
     * @param $product
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductGroupPrice($productPrice, $groupCustomer, $product)
    {
        $websiteId        = $this->storeManager->getWebsite()->getData('website_id');
        $productGroupData = $product->getData('mageworx_group_price');
        if (!empty($productGroupData)) {
            foreach ($productGroupData as $value) {
                if ($value['is_all_groups'] == 1) {
                    $productPrice = $value['group_price'];
                }
                if (($value['website_id'] == $websiteId || $value['website_id'] == HelperData::ALL_WEBSITE)
                    && $value['cust_group'] == $groupCustomer) {
                    $productPrice = $value['group_price'];
                }
            }
        }

        return $productPrice;
    }

    /**
     * @param $productPrice
     * @param $groupCustomer
     * @param $product
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductGroupSpecialPrice($productPrice, $groupCustomer, $product)
    {
        $websiteId        = $this->storeManager->getWebsite()->getData('website_id');
        $productGroupData = $product->getData('mageworx_special_group_price');
        if (!empty($productGroupData)) {
            foreach ($productGroupData as $value) {
                if ($value['is_all_groups'] == 1) {
                    $productPrice = $value['group_price'];
                }
                if (($value['website_id'] == $websiteId || $value['website_id'] == HelperData::ALL_WEBSITE)
                    && $value['cust_group'] == $groupCustomer) {
                    $productPrice = $value['group_price'];
                }
            }
        }

        return $productPrice;
    }

    /**
     * @param $priceProduct
     * @param $customPrice
     * @param $type
     *
     * @return float|int
     */
    protected function getGroupPrice($priceProduct, $customPrice, $type)
    {
        // fixed price
        if ($type == 0) {
            $priceProduct = $priceProduct + (float)$customPrice;

            return $priceProduct;
        } else {
            $percent = 1 + (float)$customPrice / 100;

            return $percent * $priceProduct;
        }
    }
}