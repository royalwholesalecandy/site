<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Model\ImportExport;

use \Magento\Framework\DataObject\Factory as DataObjectFactory;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices as CustomerGroupPricesResourceModel;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ExportHandler
 *
 */
class ExportHandler implements \MageWorx\CustomerGroupPrices\Api\ExportHandlerInterface
{
    const SKU                 = 'sku';
    const WEBSITE_ID          = 'website_id';
    const GROUP_NAME_ID       = 'group_name_id';
    const GROUP_PRICE         = 'group_price';
    const GROUP_SPECIAL_PRICE = 'group_special_price';

    const PRICE_TYPE_DEFAULT          = 0;
    const PRICE_TYPE                  = 1;
    const ABSOLUTE_PRICE_TYPE_DEFAULT = 0;
    const ABSOLUTE_PRICE_TYPE         = 1;
    const ALL_GROUPS                  = 1;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var CustomerGroupPricesResourceModel
     */
    protected $customerGroupPricesResourceModel;

    /**
     * ExpressExportHandler constructor.
     *
     * @param DataObjectFactory $dataObjectFactory
     * @param CustomerGroupPricesResourceModel $customerGroupPricesResourceModel
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        CustomerGroupPricesResourceModel $customerGroupPricesResourceModel
    ) {
        $this->dataObjectFactory                = $dataObjectFactory;
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
    }

    /**
     * Get content as a CSV string
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getContent()
    {
        $headers  = $this->getHeaders();
        $template = $this->getStringCsvTemplate($headers);
        // Add header (titles)
        $content[] = $headers->toString($template);

        $customerGroupPrices = $this->customerGroupPricesResourceModel->getExportCustomerGroupPrices();
        $customerGroupPrices = $this->combineGroupPriceAndGroupSpecialPrice($customerGroupPrices);

        foreach ($customerGroupPrices as $groupPrices) {
            if (is_array($groupPrices)) {
                foreach ($groupPrices as $key => $dataPrice) {
                    $groupPrices[$key] = '"' . $dataPrice . '"';
                }

                $content[] = implode(",", $groupPrices);
            }
        }

        $contentAsAString = implode("\n", $content);

        return $contentAsAString;
    }

    /**
     * Group Price And Group Special Price in one row (In database group price and group special price are stored in
     * different lines)
     *
     * @param array $customerGroupPrices
     * @return array
     * @throws LocalizedException
     */
    private function combineGroupPriceAndGroupSpecialPrice($customerGroupPrices)
    {
        $combinePricesArray = [];
        foreach ($customerGroupPrices as $groupPrices) {
            $groupName = ($groupPrices['is_all_groups'] == self::ALL_GROUPS) ? '*' : $groupPrices['group_name'];

            $idElementArray = $this->getIdCombinePricesArray(
                $combinePricesArray,
                $groupPrices['sku'],
                $groupName,
                $groupPrices['website_id']
            );
            if (!is_null($idElementArray)) {
                if (empty($combinePricesArray[$idElementArray]['price'])) {
                    $combinePricesArray[$idElementArray]['price'] = $this->getPriceWithPriceType(
                        $groupPrices['price'],
                        $groupPrices['price_type'],
                        $groupPrices['absolute_price_type']
                    );
                }

                if (empty($combinePricesArray[$idElementArray]['special_price'])) {
                    $combinePricesArray[$idElementArray]['special_price'] = $this->getSpecialPriceWithPriceType(
                        $groupPrices['price'],
                        $groupPrices['price_type'],
                        $groupPrices['absolute_price_type']
                    );
                }
            } else {
                $combinePricesArray[] = [
                    'sku'           => $groupPrices['sku'],
                    'website_id'    => $groupPrices['website_id'],
                    'group_name'    => $groupName,
                    'price'         => $this->getPriceWithPriceType(
                        $groupPrices['price'],
                        $groupPrices['price_type'],
                        $groupPrices['absolute_price_type']
                    ),
                    'special_price' => $this->getSpecialPriceWithPriceType(
                        $groupPrices['price'],
                        $groupPrices['price_type'],
                        $groupPrices['absolute_price_type']
                    )
                ];
            }
        }

        return $combinePricesArray;
    }

    /**
     * Get id element if has element in array
     *
     * @param array $resultGroupPrices
     * @param string $sku
     * @param int $groupName
     * @param int $websiteId
     * @return int|null
     */
    private function getIdCombinePricesArray($resultGroupPrices, $sku, $groupName, $websiteId)
    {
        foreach ($resultGroupPrices as $key => $datum) {
            if ($datum['sku'] == $sku && $datum['group_name'] == $groupName && $datum['website_id'] == $websiteId) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param string $price
     * @param int $priceType
     * @param int $absolutePriceType
     * @return string
     */
    private function getPriceWithPriceType($price, $priceType, $absolutePriceType)
    {
        // prepare product price
        if ($absolutePriceType == self::ABSOLUTE_PRICE_TYPE) {
            return '';
        }

        if ($priceType == self::PRICE_TYPE) {
            return $price . '%';
        }

        return $price;
    }

    /**
     * @param string $price
     * @param int $priceType
     * @param int $absolutePriceType
     * @return string
     */
    private function getSpecialPriceWithPriceType($price, $priceType, $absolutePriceType)
    {
        // prepare special product price
        if ($absolutePriceType == self::ABSOLUTE_PRICE_TYPE_DEFAULT) {
            return '';
        }

        if ($priceType == self::PRICE_TYPE) {
            return $price . '%';
        }

        return $price;
    }

    /**
     * Create data template from headers
     *
     * @param \Magento\Framework\DataObject $headers
     * @return string
     */
    private function getStringCsvTemplate(\Magento\Framework\DataObject $headers)
    {
        $data         = $headers->getData();
        $templateData = [];
        foreach ($data as $propertyKey => $value) {
            $templateData[] = '"{{' . $propertyKey . '}}"';
        }
        $template = implode(',', $templateData);

        return $template;
    }

    /**
     * Get headers for the selected entities
     *
     * @return \Magento\Framework\DataObject
     */
    private function getHeaders()
    {
        $dataFields = [
            static::SKU                 => __('Product SKU'),
            static::WEBSITE_ID          => __('Website ID'),
            static::GROUP_NAME_ID       => __('Group Name/ID'),
            static::GROUP_PRICE         => __('Group Price'),
            static::GROUP_SPECIAL_PRICE => __('Group Special Price')
        ];

        $dataObject = $this->dataObjectFactory->create($dataFields);

        return $dataObject;
    }
}