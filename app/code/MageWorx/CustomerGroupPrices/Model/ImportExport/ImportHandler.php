<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Model\ImportExport;

use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices as CustomerGroupPricesResourceModel;
use Magento\Framework\Indexer\IndexerRegistry;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use MageWorx\CustomerGroupPrices\Helper\Group as HelperGroup;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroupCollection;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use MageWorx\CustomerGroupPrices\Api\ImportHandlerInterface;

class ImportHandler extends \MageWorx\CustomerGroupPrices\Model\Import implements ImportHandlerInterface
{
    /**
     * @var CustomerGroupPricesResourceModel
     */
    private $customerGroupPricesResourceModel;

    /**
     * @var HelperGroup
     */
    protected $helperGroup;

    /**
     * @var CustomerCollection
     */
    private $customerCollection;

    /**
     * @var array
     */
    private $customerGroupPricesData = [];

    /**
     * ImportHandler constructor.
     *
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param ProductCollection $productCollection
     * @param CustomerGroupPricesResourceModel $customerGroupPricesResourceModel
     * @param IndexerRegistry $indexerRegistry
     * @param HelperData $helperData
     * @param HelperGroup $helperGroup
     * @param CustomerCollection $customerCollection
     * @param CustomerGroupCollection $customerGroup
     * @param \Magento\Framework\Escaper $escaper
     * @param ProductResourceModel $productResourceModel
     * @throws LocalizedException
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        ProductCollection $productCollection,
        CustomerGroupPricesResourceModel $customerGroupPricesResourceModel,
        IndexerRegistry $indexerRegistry,
        HelperData $helperData,
        HelperGroup $helperGroup,
        CustomerCollection $customerCollection,
        CustomerGroupCollection $customerGroup,
        \Magento\Framework\Escaper $escaper,
        ProductResourceModel $productResourceModel
    ) {
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
        $this->helperGroup                      = $helperGroup;
        $this->customerCollection               = $customerCollection;

        $this->customerGroupPricesData = $this->customerGroupPricesResourceModel->getAllCustomerGroupPricesData();

        parent::__construct(
            $indexerRegistry,
            $csvProcessor,
            $productCollection,
            $customerGroupPricesResourceModel,
            $helperData,
            $escaper,
            $productResourceModel
        );
    }

    /**
     * @param array $csvData . Format csv file "Product SKU","Website ID","Group Name/ID"," Group Price","Group Special
     * @return array
     * @throws LocalizedException
     */
    protected function prepareDataToDelete($csvData)
    {
        $deleteData = [];

        foreach ($csvData as $datum) {
            $productId      = $this->getProductIdBySku($datum['sku']);
            $groupsIdsArray = ($datum['group_name_id'] == '*') ? $this->getAllGroupsIds() : [
                $this->getGroupId(
                    $datum['group_name_id']
                )
            ];
            $isAllGroups    = ($datum['group_name_id'] == '*') ? self::ALL_GROUPS : self::ALL_GROUPS_DEFAULT;
            $websiteId      = $datum['website_id'];

            if (is_null($productId) || empty($groupsIdsArray) || is_null($websiteId)) {
                continue;
            }

            // group price
            if (!empty($datum['group_price'])) {
                $deleteGroupPrice = $this->getEntityIdsForDelete(
                    $this->customerGroupPricesData,
                    $productId,
                    $groupsIdsArray,
                    $websiteId,
                    self::ABSOLUTE_PRICE_TYPE_DEFAULT,
                    $isAllGroups
                );
                if (!empty($deleteGroupPrice)) {
                    $deleteData[] = $deleteGroupPrice;
                }
            }

            // special group price
            if (!empty($datum['group_special_price'])) {
                $deleteCustomerGroupPrice = $this->getEntityIdsForDelete(
                    $this->customerGroupPricesData,
                    $productId,
                    $groupsIdsArray,
                    $websiteId,
                    self::ABSOLUTE_PRICE_TYPE,
                    $isAllGroups
                );

                if (!empty($deleteCustomerGroupPrice)) {
                    $deleteData[] = $deleteCustomerGroupPrice;
                }
            }
        }

        return $deleteData;
    }

    /**
     * Prepare data to save. Format csv file "Product SKU","Website ID","Group Name/ID"," Group Price","Group Special
     * Price"
     *
     * @param array $csvData
     * @return array
     * @throws \Exception
     */
    protected function prepareDataToSave($csvData)
    {
        $saveData = [];
        foreach ($csvData as $datum) {
            $productId = $this->getProductIdBySku($datum['sku']);
            if ($datum['group_name_id'] == '*') {
                $isAllGroups = self::ALL_GROUPS;
                $groupId     = self::GROUP_ID_DEFAULT;
            } else {
                $isAllGroups = self::ALL_GROUPS_DEFAULT;
                $groupId     = $this->getGroupId($datum['group_name_id']);

            }

            if (is_null($productId) || is_null($groupId)) {
                continue;
            }

            // save Group Price
            if (!empty($datum['group_price'])) {
                $saveData[] = array(
                    'product_id'          => $productId,
                    'group_id'            => $groupId,
                    'is_all_groups'       => $isAllGroups,
                    'website_id'          => $datum['website_id'],
                    'math_sign'           => $this->helperData->getMathSign($datum['group_price']),
                    'price'               => $this->getOriginalPrice($datum['group_price']),
                    'price_type'          => $this->getPriceType($datum['group_price']),
                    'absolute_price_type' => self::ABSOLUTE_PRICE_TYPE_DEFAULT,
                    'assign_price'        => self::ASSIGN_PRICE,
                    'is_manual'           => self::MANUAL
                );
            }

            // save Group Special Price
            if (!empty($datum['group_special_price'])) {
                $saveData[] = array(
                    'product_id'          => $productId,
                    'group_id'            => $groupId,
                    'is_all_groups'       => $isAllGroups,
                    'website_id'          => $datum['website_id'],
                    'math_sign'           => $this->helperData->getMathSign($datum['group_special_price']),
                    'price'               => $this->getOriginalPrice($datum['group_special_price']),
                    'price_type'          => $this->getPriceType($datum['group_special_price']),
                    'absolute_price_type' => self::ABSOLUTE_PRICE_TYPE,
                    'assign_price'        => self::ASSIGN_PRICE,
                    'is_manual'           => self::MANUAL
                );
            }
        }

        $needCreate = [];
        foreach ($saveData as $data) {
            if ($data['is_all_groups'] == self::ALL_GROUPS) {
                $customerGroupIds       = $this->helperGroup->getCustomersGroup();
                $customerGroupedIdsUsed = $this->getUsedCustomersGroup(
                    $saveData,
                    $data['product_id'],
                    $data['website_id'],
                    $data['absolute_price_type'],
                    $data['is_all_groups']
                );

                /* from $customerGroupIds & $customerGroupedIdsUsed - get array ids which need create */
                $idsGroupCreate = array_diff($customerGroupIds, $customerGroupedIdsUsed);
                foreach ($idsGroupCreate as $idGroup) {
                    $needCreate[] = array(
                        'product_id'          => $data['product_id'],
                        'group_id'            => $idGroup,
                        'is_all_groups'       => self::ALL_GROUPS_DEFAULT,
                        'website_id'          => $data['website_id'],
                        'math_sign'           => $data['math_sign'],
                        'price'               => $data['price'],
                        'price_type'          => $data['price_type'],
                        'absolute_price_type' => $data['absolute_price_type'],
                        'assign_price'        => self::ASSIGN_PRICE,
                        'is_manual'           => self::MANUAL_DEFAULT
                    );
                }
            }
        }

        $saveData = array_merge($saveData, $needCreate);

        return $saveData;
    }

    /**
     * @param array $pricesData
     * @param int $productId
     * @param int $websiteId
     * @param int $absolutePriceType
     * @param int $groupId
     * @param int $isAllGroups
     * @return array
     */
    protected function getUsedCustomersGroup(
        $pricesData,
        $productId,
        $websiteId,
        $absolutePriceType,
        $isAllGroups
    ) {
        $ids = [];

        if ($isAllGroups == self::ALL_GROUPS) {
            foreach ($this->customerGroupPricesData as $data) {
                if ($data['product_id'] == $productId
                    && $data['is_all_groups'] == self::ALL_GROUPS_DEFAULT
                    && $data['is_manual'] == self::MANUAL
                    && $data['absolute_price_type'] == $absolutePriceType
                ) {
                    $ids[] = $data['group_id'];
                }
            }
        }

        foreach ($pricesData as $item) {
            if ($item['is_all_groups'] != self::ALL_GROUPS
                && $item['product_id'] == $productId
                && $item['website_id'] == $websiteId
                && $item['absolute_price_type'] == $absolutePriceType
            ) {
                $ids[] = $item['group_id'];
            }
        }

        return $ids;
    }

    /**
     * @param array $rawData
     * @return array
     * @throws LocalizedException
     */
    protected function getDataFormCSV($rawData)
    {
        $prepareData = [];
        foreach ($rawData as $dataIndex => $datum) {
            // skip headers
            if ($dataIndex == 0) {
                continue;
            }

            if (is_array($datum) && count($datum) == 5) {
                $prepareData[] = [
                    self::SKU                 => $datum[0],
                    self::WEBSITE_ID          => $datum[1],
                    self::GROUP_NAME_ID       => $datum[2],
                    self::GROUP_PRICE         => $datum[3],
                    self::GROUP_SPECIAL_PRICE => $datum[4],
                ];
            }
        }

        if (empty($prepareData)) {
            throw new LocalizedException(__('CSV file was parsed incorrectly'));
        }

        return $prepareData;
    }
}