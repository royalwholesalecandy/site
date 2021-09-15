<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Model\ImportExport;

use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices as CustomerGroupPricesResourceModel;
use Magento\Framework\Indexer\IndexerRegistry;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use MageWorx\CustomerGroupPrices\Model\Import as ModelImportCustomerGroupPrices;
use MageWorx\CustomerGroupPrices\Api\ImportHandlerInterface;

class ImportM1GroupSpecialPricesHandler extends ModelImportCustomerGroupPrices implements ImportHandlerInterface
{
    /**
     * @var CustomerGroupPricesResourceModel
     */
    private $customerGroupPricesResourceModel;

    /**
     * ImportM1GroupSpecialPricesHandler constructor.
     *
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param ProductCollection $productCollection
     * @param CustomerGroupPricesResourceModel $customerGroupPricesResourceModel
     * @param IndexerRegistry $indexerRegistry
     * @param HelperData $helperData
     * @param \Magento\Framework\Escaper $escaper
     * @param ProductResourceModel $productResourceModel
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        ProductCollection $productCollection,
        CustomerGroupPricesResourceModel $customerGroupPricesResourceModel,
        IndexerRegistry $indexerRegistry,
        HelperData $helperData,
        \Magento\Framework\Escaper $escaper,
        ProductResourceModel $productResourceModel
    ) {
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;

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
     * @param array $csvData . Format csv file "Group Name/ID";SKU;"Specific Price";"Website ID"
     * @return array
     * @throws LocalizedException
     */
    protected function prepareDataToDelete($csvData)
    {
        $deleteData             = [];
        $customerGroupPriceData = $this->customerGroupPricesResourceModel->getAllCustomerGroupPricesData();

        foreach ($csvData as $datum) {
            $productId = $this->getProductIdBySku($datum['sku']);
            $groupId   = [$this->getGroupId($datum['group_name_id'])];
            $websiteId = $datum['website_id'];

            if (is_null($productId) || is_null($groupId) || is_null($websiteId)) {
                continue;
            }

            // special group price
            if (!empty($datum['specific_price'])) {
                $deleteCustomerGroupPrice = $this->getEntityIdsForDelete(
                    $customerGroupPriceData,
                    $productId,
                    $groupId,
                    $websiteId,
                    self::ABSOLUTE_PRICE_TYPE
                );

                if (!empty($deleteCustomerGroupPrice)) {
                    $deleteData[] = $deleteCustomerGroupPrice;
                }
            }
        }

        return $deleteData;
    }

    /**
     * Prepare data to save. Format csv file "Group Name/ID";SKU;"Specific Price";"Website ID"
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
            $groupId   = $this->getGroupId($datum['group_name_id']);
            if (is_null($productId) || is_null($groupId)) {
                continue;
            }

            // save Group Special Price
            if (!empty($datum['specific_price'])) {
                $saveData[] = array(
                    'product_id'          => $productId,
                    'group_id'            => $groupId,
                    'is_all_groups'       => self::ALL_GROUPS_DEFAULT,
                    'website_id'          => $datum['website_id'],
                    'math_sign'           => $this->helperData->getMathSign($datum['specific_price']),
                    'price'               => $this->getOriginalPrice($datum['specific_price']),
                    'price_type'          => $this->getPriceType($datum['specific_price']),
                    'absolute_price_type' => self::ABSOLUTE_PRICE_TYPE,
                    'assign_price'        => self::ASSIGN_PRICE,
                    'is_manual'           => self::MANUAL
                );
            }
        }

        return $saveData;
    }
}