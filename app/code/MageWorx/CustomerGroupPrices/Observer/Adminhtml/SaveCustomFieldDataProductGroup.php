<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer as EventObserver;
use MageWorx\CustomerGroupPrices\Helper\Data as Helper;
use MageWorx\CustomerGroupPrices\Helper\Group as HelperGroup;
use Magento\Customer\Model\Session as CustomerSession;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices;

class SaveCustomFieldDataProductGroup implements ObserverInterface
{
    const CUST_GROUP_ALL = \Magento\Customer\Model\GroupManagement::CUST_GROUP_ALL;

    /**
     * @var Helper
     */
    protected $helperData;

    /**
     * @var HelperGroup
     */
    protected $helperGroup;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var CustomerGroupPrices
     */
    protected $customerGroupPricesResourceModel;

    /**
     * SaveCustomFieldDataProductGroup constructor.
     *
     * @param Helper $helperData
     * @param HelperGroup $helperGroup
     * @param CustomerSession $customerSession
     * @param CustomerGroupPrices $customerGroupPricesResourceModel
     */
    public function __construct(
        Helper $helperData,
        HelperGroup $helperGroup,
        CustomerSession $customerSession,
        CustomerGroupPrices $customerGroupPricesResourceModel
    ) {
        $this->helperData                       = $helperData;
        $this->helperGroup                      = $helperGroup;
        $this->customerSession                  = $customerSession;
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
    }

    /**
     * Save group price data
     *
     * @param EventObserver $observer
     *
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (!$product instanceof Product) {
            return $this;
        }

        $groupPriceData   = $product->getData('mageworx_group_price');
        $specialPriceData = $product->getData('mageworx_special_group_price');

        if (!empty($groupPriceData)) {
            /* delete dublicate */
            $groupPriceData = $this->deleteDublicate($groupPriceData);
            /* clean old group price */
            $this->customerGroupPricesResourceModel->deleteProductGroupPrice($product->getId(), Helper::GROUP_PRICE);
            $this->saveProductGroupPrice($product->getId(), $groupPriceData, Helper::GROUP_PRICE);
        }

        if (!empty($specialPriceData)) {
            $specialPriceData = $this->deleteDublicate($specialPriceData);
            /* clean old group price */
            $this->customerGroupPricesResourceModel->deleteProductGroupPrice(
                $product->getId(),
                Helper::SPECIAL_GROUP_PRICE
            );
            $this->saveProductGroupPrice($product->getId(), $specialPriceData, Helper::SPECIAL_GROUP_PRICE);
        }

        return $this;
    }

    /**
     * Save customer group prices
     *
     * @param int $productId
     * @param array $pricesData
     * @param int $absolutePriceType
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveProductGroupPrice($productId, $pricesData, $absolutePriceType)
    {
        /* create all row which create from interface */
        $prepareProductGroupPrices = [];
        foreach ($pricesData as $value) {
            $isCanSave = $this->isCanSaveRow($value);
            if ($isCanSave) {
                $groupId     = $value['cust_group'];
                $isAllGroups = 0;
                $isManual    = 1;

                if ($value['cust_group'] == self::CUST_GROUP_ALL) {
                    $groupId     = 0;
                    $isAllGroups = 1;
                }

                $prepareProductGroupPrices[] = array(
                    'product_id'          => $productId,
                    'group_id'            => $groupId,
                    'is_all_groups'       => $isAllGroups,
                    'website_id'          => $value['website_id'],
                    'math_sign'           => $this->helperData->getMathSign($value['group_price']),
                    'price'               => $value['group_price'],
                    'price_type'          => $value['group_type_price'],
                    'absolute_price_type' => $absolutePriceType,
                    'assign_price'        => '1',
                    'is_manual'           => $isManual
                );
            }
        }

        if (!empty($prepareProductGroupPrices)) {
            $this->customerGroupPricesResourceModel->saveMultipleProductGroupPrice($prepareProductGroupPrices);
        }

        $websitesIds = $this->getWebsitesIdsFromPricesData($pricesData);
        foreach ($websitesIds as $website) {
            /* filter $pricesData by $websitesIds*/
            $filterPriceData = $this->filterPricesDataByWebsitesId($pricesData, $website);

            $isUsedPriceForAllGroups = $this->isUsedOnProductAllGroups($filterPriceData);
            if (!$isUsedPriceForAllGroups) {
                continue;
            }

            $customerGroupIds       = $this->helperGroup->getCustomersGroup();
            $customerGroupedIdsUsed = $this->getUsedCustomersGroup($filterPriceData);

            /* from $customerGroupIds & $customerGroupedIdsUsed - get array ids which need create */
            $idsGroupCreate = array_diff($customerGroupIds, $customerGroupedIdsUsed);
            $isManual       = 0;
            $allGroups      = 0;
            foreach ($idsGroupCreate as $groupId) {
                foreach ($filterPriceData as $value) {
                    if ($value['cust_group'] != self::CUST_GROUP_ALL) {
                        continue;
                    }
                    $this->customerGroupPricesResourceModel->saveProductGroupPrice(
                        $productId,
                        $value['website_id'],
                        $value['group_price'],
                        $value['group_type_price'],
                        $absolutePriceType,
                        $groupId,
                        $allGroups,
                        $isManual
                    );
                }
            }
        }
        unset($prepareProductGroupPrice);
    }

    /**
     * Filter priceData by website
     *
     * @param $pricesData
     * @param $websitesId
     *
     * @return mixed
     */
    protected function filterPricesDataByWebsitesId($pricesData, $websitesId)
    {
        foreach ($pricesData as $key => $value) {
            if ($value['website_id'] != $websitesId) {
                unset($pricesData[$key]);
            }
        }

        return $pricesData;
    }

    /**
     * Delete dublicate from priceData
     *
     * @param $groupPriceData
     *
     * @return mixed
     */
    protected function deleteDublicate($groupPriceData)
    {
        $ids = [];
        for ($i = 0; $i < count($groupPriceData); $i++) {
            $valueGroup     = $groupPriceData[$i]['cust_group'];
            $valueWebsiteId = $groupPriceData[$i]['website_id'];
            for ($j = $i + 1; $j < count($groupPriceData); $j++) {
                if ($valueGroup == $groupPriceData[$j]['cust_group']
                    && $valueWebsiteId == $groupPriceData[$j]['website_id']
                ) {
                    array_push($ids, $j);
                }
            }
        }
        $ids = array_unique($ids);
        if (!empty($ids)) {
            /* delete dublicate */
            foreach ($groupPriceData as $key => $value) {
                foreach ($ids as $id) {
                    if ($key == $id) {
                        unset($groupPriceData[$key]);
                    }
                }
            }
        }

        return $groupPriceData;
    }

    /**
     * Get array Website id from price data
     *
     * @param $pricesData
     *
     * @return array
     */
    protected function getWebsitesIdsFromPricesData($pricesData)
    {
        $ids = [];
        foreach ($pricesData as $item) {
            $ids[] = $item['website_id'];
        }
        $ids = array_unique($ids);

        return $ids;
    }

    /**
     * Get used ids
     *
     * @param $pricesData
     *
     * @return array
     */
    protected function getUsedCustomersGroup($pricesData)
    {
        $ids = [];
        foreach ($pricesData as $item) {
            if ($item['cust_group'] != self::CUST_GROUP_ALL) {
                $ids[] = $item['cust_group'];
            }
        }

        return $ids;
    }

    /**
     * @param $pricesData
     *
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function isUsedOnProductAllGroups($pricesData)
    {
        foreach ($pricesData as $value) {
            $isCanSave = $this->isCanSaveRow($value);
            if ($isCanSave) {
                if ($value['cust_group'] == self::CUST_GROUP_ALL) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $value
     *
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function isCanSaveRow($value)
    {
        if (version_compare(
            $this->helperData->getModuleVersion('Magento_Ui'),
            '100.1.9',
            '>='
        )) {
            if (array_key_exists('initialize', $value) || array_key_exists('record_id', $value)) {
                if (array_key_exists('delete', $value)) {
                    return false;
                }

                return true;
            } else {
                return false;
            }
        }

        return empty($value['delete']);
    }
}