<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices as CustomerGroupPricesResourceModel;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\Indexer\IndexerRegistry;

abstract class Import
{
    const GROUP_NAME_ID       = 'group_name_id';
    const SKU                 = 'sku';
    const SPECIFIC_PRICE      = 'specific_price';
    const WEBSITE_ID          = 'website_id';
    const GROUP_PRICE         = 'group_price';
    const GROUP_SPECIAL_PRICE = 'group_special_price';

    const ABSOLUTE_PRICE_TYPE_DEFAULT = 0;
    const ABSOLUTE_PRICE_TYPE         = 1;
    const ALL_GROUPS_DEFAULT          = 0;
    const ALL_GROUPS                  = 1;
    const GROUP_ID_DEFAULT            = 0;
    const ASSIGN_PRICE_DEFAULT        = 0;
    const ASSIGN_PRICE                = 1;
    const MANUAL_DEFAULT              = 0;
    const MANUAL                      = 1;

    const CUST_GROUP_ALL = \Magento\Customer\Model\GroupManagement::CUST_GROUP_ALL;

    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csvProcessor;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @var CustomerGroupPricesResourceModel
     */
    private $customerGroupPricesResourceModel;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var ProductResourceModel
     */
    protected $productResourceModel;

    /**
     * @var array
     */
    protected $productsDataBySku = [];

    /**
     * @var array
     */
    protected $groupData = [];

    /**
     * @var array
     */
    protected $groupsIds = [];

    /**
     * Import constructor.
     *
     * @param IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param ProductCollection $productCollection
     * @param CustomerGroupPricesResourceModel $customerGroupPricesResourceModel
     * @param HelperData $helperData
     * @param \Magento\Framework\Escaper $escaper
     * @param ProductResourceModel $productResourceModel
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        \Magento\Framework\File\Csv $csvProcessor,
        ProductCollection $productCollection,
        CustomerGroupPricesResourceModel $customerGroupPricesResourceModel,
        HelperData $helperData,
        \Magento\Framework\Escaper $escaper,
        ProductResourceModel $productResourceModel
    ) {
        $this->indexerRegistry                  = $indexerRegistry;
        $this->csvProcessor                     = $csvProcessor;
        $this->productCollection                = $productCollection;
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
        $this->helperData                       = $helperData;
        $this->escaper                          = $escaper;
        $this->productResourceModel             = $productResourceModel;
        $this->groupData                        = $this->customerGroupPricesResourceModel->getAllCustomerGroupsData();
    }

    /**
     * @param array $file file info retrieved from $_FILES array
     * @throws LocalizedException
     */
    public function importFromCsvFile($file)
    {
        if (!isset($file['tmp_name'])
            || !isset($file['type'])
            || !in_array($file['type'], $this->helperData->getAllowedFormatFile())
        ) {
            throw new LocalizedException(__('Invalid file upload attempt.'));
        }

        $rawData = $this->csvProcessor->getData($file['tmp_name']);

        if (empty($rawData)) {
            throw new LocalizedException(__('CSV file was parsed incorrectly.'));
        }

        $dataFromCSV = $this->getDataFormCSV($rawData);

        if ($this->validateData($dataFromCSV)) {
            $dataToDeleteGroupPrice = $this->prepareDataToDelete($dataFromCSV);
            $dataToDeleteGroupPrice = $this->getDataForDeleteWithoutDuplicate($dataToDeleteGroupPrice);

            $dataToSaveGroupPrice = $this->prepareDataToSave($dataFromCSV);
            $dataToSaveGroupPrice = $this->getDataForSaveWithoutDuplicate($dataToSaveGroupPrice);

            if (!empty($dataToDeleteGroupPrice)) {
                $this->customerGroupPricesResourceModel->deleteGroupPrices($dataToDeleteGroupPrice);
            }

            if (!empty($dataToSaveGroupPrice)) {
                $this->customerGroupPricesResourceModel->saveMultipleProductGroupPrice($dataToSaveGroupPrice);

                /* reindex catalog_product_price */
                $this->indexerRegistry->get(Processor::INDEXER_ID)->invalidate();
            }
        }
    }

    /**
     * @param array $dataFromCSV
     * @return mixed
     */
    protected abstract function prepareDataToDelete($dataFromCSV);

    /**
     * @param array $dataFromCSV
     * @return mixed
     */
    protected abstract function prepareDataToSave($dataFromCSV);

    /**
     * @param array $dataFromCSV
     * @return bool
     * @throws \Exception
     */
    protected function validateData($dataFromCSV)
    {
        $this->validateGroupNames($dataFromCSV);
        $this->validateGroupIds($dataFromCSV);
        $this->validateWebsiteIds($dataFromCSV);
        $this->validateSkus($dataFromCSV);
        $this->validateDuplicatedData($dataFromCSV);

        return true;
    }

    /**
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateDuplicatedData($data)
    {
        $validatedPricesArray = [];
        $duplicatedSkuArray   = [];
        foreach ($data as $value) {
            $groupId = $this->getGroupId($value['group_name_id']);
            $newKey  = $value['sku'] . '-' . $groupId . '-' . $value['website_id'];

            if (!array_key_exists($newKey, $validatedPricesArray)) {
                $validatedPricesArray[$newKey] = $value;
            } else {
                $duplicatedSkuArray[] = $value['sku'];
            }
        }

        if (!empty($duplicatedSkuArray)) {
            $invalidDuplicatedSkusAsString = $this->escaper->escapeHtml(implode(", ", $duplicatedSkuArray));
            throw new LocalizedException(
                __('Products with the following SKUs have duplicate records: %1', $invalidDuplicatedSkusAsString)
            );
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    protected function validateSkus($data)
    {
        $skusArray = [];

        foreach ($data as $value) {
            $skusArray[] = $value['sku'];
        }

        $uniqueSkusArray         = array_unique($skusArray);
        $productCollection       = $this->productCollection->create()
                                                           ->addFieldToFilter('sku', ['in' => $uniqueSkusArray]);
        $allSkuArray             = $productCollection->getColumnValues('sku');
        $this->productsDataBySku = $this->productResourceModel->getProductsIdsBySkus($allSkuArray);

        $invalidSkusArray = array_unique(array_diff($uniqueSkusArray, $allSkuArray));

        if (!empty($invalidSkusArray)) {
            $invalidSkuAsString = $this->escaper->escapeHtml(implode(", ", $invalidSkusArray));
            throw new LocalizedException(__('Products with the following SKUs do not exist: %1', $invalidSkuAsString));
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    protected function validateWebsiteIds($data)
    {
        $websiteIdsArray = [];
        foreach ($data as $value) {
            $websiteIdsArray[] = $value['website_id'];
        }

        $allWebsiteIds = $this->customerGroupPricesResourceModel->getAllWebsiteIds();
        foreach ($allWebsiteIds as $data) {
            $prepareAllWebsiteIds[] = $data['website_id'];
        }

        $invalidWebsiteIdsArray = array_unique(array_diff($websiteIdsArray, $prepareAllWebsiteIds));

        if (!empty($invalidWebsiteIdsArray)) {
            $invalidWebsiteIdsAsString = $this->escaper->escapeHtml(implode(", ", $invalidWebsiteIdsArray));
            throw new LocalizedException(__('The requested Website ID(s) not found: %1', $invalidWebsiteIdsAsString));
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    protected function validateGroupNames($data)
    {
        $prepareGroupsNamesArray = [];
        $groupsNamesArray        = [];

        foreach ($data as $value) {
            if (!is_numeric($value['group_name_id'])) {
                $groupsNamesArray[] = $value['group_name_id'];
            }
        }

        $allGroupsNames = $this->customerGroupPricesResourceModel->getAllCustomerGroupsName();
        foreach ($allGroupsNames as $data) {
            $prepareGroupsNamesArray[] = $data['customer_group_code'];
        }
        // add all groups
        $prepareGroupsNamesArray[] = '*';

        $invalidGroupsNames = array_unique(array_diff($groupsNamesArray, $prepareGroupsNamesArray));

        if (!empty($invalidGroupsNames)) {
            $invalidGroupsNameAsString = $this->escaper->escapeHtml(implode(", ", $invalidGroupsNames));
            throw new LocalizedException(
                __('The requested customer group name(s) not found: %1', $invalidGroupsNameAsString)
            );
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    protected function validateGroupIds($data)
    {
        $groupsIdsArray = [];

        foreach ($data as $value) {
            if (is_numeric($value['group_name_id'])) {
                $groupsIdsArray[] = $value['group_name_id'];
            }
        }

        $allGroupsIds = $this->customerGroupPricesResourceModel->getAllCustomerGroupsId();
        foreach ($allGroupsIds as $data) {
            $prepareAllGroupsId[] = $data['customer_group_id'];
        }
        // add all groups
        $prepareAllGroupsId[] = '*';

        $invalidGroupsIdsArray = array_unique(array_diff($groupsIdsArray, $prepareAllGroupsId));

        if (!empty($invalidGroupsIdsArray)) {
            $invalidGroupsIdsAsString = $this->escaper->escapeHtml(implode(", ", $invalidGroupsIdsArray));
            throw new LocalizedException(
                __('The requested customer group ID(s) not found: %1', $invalidGroupsIdsAsString)
            );
        }
    }

    /**
     * @param array $customerGroupPriceData
     * @param int $productId
     * @param array $groupsIdsArray
     * @param int $websiteId
     * @param int $absolutePriceType
     * @param int $isAllGroups
     * @return array
     */
    protected function getEntityIdsForDelete(
        $customerGroupPriceData,
        $productId,
        $groupsIdsArray,
        $websiteId,
        $absolutePriceType,
        $isAllGroups = 0
    ) {
        $entityArray = [];

        foreach ($customerGroupPriceData as $datum) {
            $isDelete = ($isAllGroups == self::ALL_GROUPS && $datum['is_manual'] == self::MANUAL) ? false : true;
            if ($datum['product_id'] == $productId
                && (in_array($datum['group_id'], $groupsIdsArray))
                && $datum['website_id'] == $websiteId
                && $datum['absolute_price_type'] == $absolutePriceType
                && $isDelete
            ) {
                $entityArray[] = $datum['entity_id'];
            }
        }

        //add row with group_id = 0 and is_all_groups = 1
        if ($isAllGroups == self::ALL_GROUPS) {
            foreach ($customerGroupPriceData as $datum) {
                if ($datum['product_id'] == $productId
                    && $datum['is_all_groups'] == self::ALL_GROUPS
                    && $datum['website_id'] == $websiteId
                    && $datum['absolute_price_type'] == $absolutePriceType
                ) {
                    $entityArray[] = $datum['entity_id'];
                }
            }
        }

        return $entityArray;
    }

    /**
     * @param string $sku
     * @return int|null
     * @throws \Exception
     */
    protected function getProductIdBySku($sku)
    {
        if (!empty($this->productsDataBySku[$sku])) {
            return $this->productsDataBySku[$sku];
        }

        return null;
    }

    /**
     * @param string $groupNameId
     * @return int|null
     */
    protected function getGroupId($groupNameId)
    {
        if (is_numeric($groupNameId)) {
            return $groupNameId;
        }

        if (empty($this->groupData)) {
            $this->groupData = $this->customerGroupPricesResourceModel->getAllCustomerGroupsData();
        }

        foreach ($this->groupData as $data) {
            if ($data['customer_group_code'] == $groupNameId) {
                return $data['customer_group_id'];
            }
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getAllGroupsIds()
    {
        if (empty($this->groupData)) {
            $this->groupData = $this->customerGroupPricesResourceModel->getAllCustomerGroupsData();
        }

        if (empty($groupsIds)) {
            if (!is_array($this->groupData)) {
                return [];
            }
            foreach ($this->groupData as $data) {
                $this->groupsIds[] = $data['customer_group_id'];
            }
        }

        return $this->groupsIds;
    }

    /**
     * @param string $price
     * @return int
     */
    protected function getPriceType($price)
    {
        if (strripos($price, '%') !== false) {
            return 1;
        }

        return 0;
    }

    /**
     * Return price without symbol %
     *
     * @param string $price
     * @return int
     */
    protected function getOriginalPrice($price)
    {
        if (strripos($price, '%') !== false) {
            return str_replace('%', '', $price);
        }

        return $price;
    }


    /**
     * @param array $rawData
     * @return array
     * @throws \Exception
     */
    protected function getDataFormCSV($rawData)
    {
        $prepareData = [];

        $delimiter = $this->getUseDelimiter($rawData);
        if (is_null($delimiter)) {
            throw new LocalizedException(__('Please use comma or semicolon as delimiter while export in Magento 1.'));
        }

        $tempArray = [];
        foreach ($rawData as $dataIndex => $data) {
            // skip headers
            if ($dataIndex == 0) {
                continue;
            }
            if ($delimiter == ',') {
                $tempArray[] = $data;
            } else {
                $tempArray[] = explode($delimiter, $data[0]);
            }
        }
        $rawData = $tempArray;

        foreach ($rawData as $dataIndex => $datum) {
            if (is_array($datum) && count($datum) == 4) {
                $prepareData[] = [
                    self::GROUP_NAME_ID  => $datum[0],
                    self::SKU            => $datum[1],
                    self::SPECIFIC_PRICE => $datum[2],
                    self::WEBSITE_ID     => $datum[3],
                ];
            }
        }

        if (empty($prepareData)) {
            throw new LocalizedException(__('CSV file was parsed incorrectly.'));
        }

        return $prepareData;
    }

    /**
     * @param array $saveData
     * @return array
     */
    protected function getDataForSaveWithoutDuplicate($saveData)
    {
        $checkDataToSave = [];

        foreach ($saveData as $datum) {
            $newKey = $datum['product_id'] . '-' . $datum['group_id'] . '-' .
                $datum['is_all_groups'] . '-' . $datum['website_id'] . '-' . $datum['absolute_price_type'];

            if (!array_key_exists($newKey, $checkDataToSave)) {
                $checkDataToSave[$newKey] = $datum;
            }
        }

        return $checkDataToSave;
    }

    /**
     * Get using  delimiter
     *
     * @param array $rawData
     * @return string|null
     */
    protected function getUseDelimiter($rawData)
    {
        if (!empty($rawData) && !empty($rawData[0])) {
            //define correct delimiter by header csv
            $headData = $rawData[0];

            if (is_array($headData) && count($headData) == 4) {
                return ',';
            }

            if (strpos($headData[0], ',') !== false && count(explode(",", $headData[0])) == 4) {
                return ',';
            }

            if (strpos($headData[0], ';') !== false && count(explode(";", $headData[0])) == 4) {
                return ';';
            }
        }

        return null;
    }

    /**
     * @param array $deleteData
     * @return array
     */
    protected function getDataForDeleteWithoutDuplicate($deleteData)
    {
        if (!empty($deleteData)) {
            $deleteData = call_user_func_array('array_merge', $deleteData);
            $deleteData = array_unique($deleteData);
        }

        return $deleteData;
    }
}