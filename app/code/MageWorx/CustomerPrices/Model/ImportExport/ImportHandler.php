<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Model\ImportExport;

use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use MageWorx\CustomerPrices\Helper\Data as HelperData;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as CustomerPricesResourceModel;
use Magento\Framework\Indexer\IndexerRegistry;

class ImportHandler
{
    const ENTITY_ID     = 'customer_id';
    const EMAIL         = 'email';
    const SKU           = 'sku';
    const QTY           = 'qty';
    const PRICE         = 'price';
    const SPECIAL_PRICE = 'special_price';

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var array|CustomerPricesInterface[]
     */
    protected $customerPrices;

    /**
     * @var CustomerCollection
     */
    protected $customerCollection;

    /**
     * @var ProductCollection
     */
    protected $productCollection;

    /**
     * @var HelperCalculate
     */
    protected $helperCalculate;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var CustomerPricesResourceModel
     */
    protected $customerModel;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * ImportHandler constructor.
     *
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param CustomerCollection $customerCollection
     * @param ProductCollection $productCollection
     * @param HelperCalculate $helperCalculate
     * @param HelperData $helperData
     * @param CustomerPricesResourceModel $customerModel
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        CustomerCollection $customerCollection,
        ProductCollection $productCollection,
        HelperCalculate $helperCalculate,
        HelperData $helperData,
        CustomerPricesResourceModel $customerModel,
        IndexerRegistry $indexerRegistry
    ) {
        $this->csvProcessor       = $csvProcessor;
        $this->customerCollection = $customerCollection;
        $this->productCollection  = $productCollection;
        $this->helperCalculate    = $helperCalculate;
        $this->helperData         = $helperData;
        $this->customerModel      = $customerModel;
        $this->indexerRegistry    = $indexerRegistry;
    }

    /**
     * Import  CSV file
     *
     * @param array $file file info retrieved from $_FILES array
     * @return void
     * @throws LocalizedException
     * @throws \Exception
     */
    public function importFromCsvFile($file)
    {
        if (!isset($file['tmp_name'])
            || !isset($file['type'])
            || !in_array($file['type'], $this->getAllowedFormatFile())) {
            throw new LocalizedException(__('Invalid file upload attempt.'));
        }

        $rawData = $this->csvProcessor->getData($file['tmp_name']);
        $this->checkCorrectEmail($rawData);
        $dataFromCSV = $this->getDataFormCSV($rawData);

        /* check correct email and get email-customerId Relation */
        $emailCustomerIdRelations = $this->getEmailCustomerIdRelation($dataFromCSV);

        /* check correct sku and and get sku-productId Relation */
        $skuProductIdRelations = $this->getSkuProductIdRelation($dataFromCSV);

        $dataToSave = $this->getCustomerPricesDataToSave(
            $dataFromCSV,
            $skuProductIdRelations,
            $emailCustomerIdRelations
        );

        $dataToDelete = $this->getCustomerPricesDataToDelete(
            $dataFromCSV,
            $skuProductIdRelations,
            $emailCustomerIdRelations
        );

        //delete customer data
        if (!empty($dataToDelete)) {
            foreach ($dataToDelete as $value) {
                $this->customerModel->deleteProductCustomerPrice($value['product_id'], $value['customer_id']);
            }
        }

        if (!empty($dataToSave)) {
            // get ids products which need save
            $productIdsSave = $this->getIdsProducts($dataToSave);
            if (!empty($productIdsSave)) {
                foreach ($productIdsSave as $id) {
                    /* set data in catalog_product_entity_decimal */
                    if (!$this->customerModel->hasSpecialAttributeByProductId($id)) {
                        $this->customerModel->addRowWithSpecialAttribute($id);
                    }
                }
            }

            /* save */
            $this->customerModel->saveCustomerProductsPrices($dataToSave);

            /* reindex catalog_product_price */
            $this->indexerRegistry->get(Processor::INDEXER_ID)->invalidate();

            if ($this->helperData->isEnabledCustomerPriceInCatalogPriceRule()) {
                $this->indexerRegistry->get(RuleProductProcessor::INDEXER_ID)->invalidate();
            }
        }
    }

    /**
     * @param array $csvData
     * @return array
     * @throws \Exception
     */
    protected function getEmailCustomerIdRelation($csvData)
    {
        $emailCustomerIdRelations = [];
        $csvListEmails            = $this->getListEmail($csvData);
        if (!is_array($csvListEmails) || empty($csvListEmails)) {
            throw new \Exception(__('Not correct format for CSV file'));
        }

        $customerCollection       = $this->customerCollection->create()
                                                             ->addFieldToFilter('email', ['in' => $csvListEmails]);
        $emailList                = $customerCollection->getColumnValues('email');
        $idList                   = $customerCollection->getColumnValues('entity_id');
        $emailCustomerIdRelations = array_combine($emailList, $idList);

        $missedEmails = array_unique(array_diff($csvListEmails, $emailList));
        if (!empty($missedEmails)) {
            $missedEmailsAsString = implode(", ", $missedEmails);
            throw new \Exception(__('The requested customer email(s) not found: %1', $missedEmailsAsString));
        }

        if (empty($emailCustomerIdRelations)) {
            throw new \Exception(__('Not correct format for CSV file'));
        }

        return $emailCustomerIdRelations;
    }

    /**
     * @param array $csvData
     * @return array
     * @throws \Exception
     */
    protected function getSkuProductIdRelation($csvData)
    {
        $skuProductIdRelations = [];
        $entityId              = $this->helperCalculate->getLinkField();
        $csvListSku            = $this->getListSku($csvData);
        if (!is_array($csvListSku) || empty($csvListSku)) {
            throw new \Exception(__('Not correct format for CSV file'));
        }

        $productCollection     = $this->productCollection->create()
                                                         ->addFieldToFilter('sku', ['in' => $csvListSku]);
        $skuList               = $productCollection->getColumnValues('sku');
        $productIdList         = $productCollection->getColumnValues($entityId);
        $skuProductIdRelations = array_combine($skuList, $productIdList);

        $missedProductSkus = array_unique(array_diff($csvListSku, $skuList));
        if (!empty($missedProductSkus)) {
            $missedProductSkusAsString = implode(", ", $missedProductSkus);
            throw new \Exception(__('Products with the following SKUs do not exist: %1', $missedProductSkusAsString));
        }

        if (empty($skuProductIdRelations)) {
            throw new \Exception(__('Not correct format for CSV file'));
        }

        return $skuProductIdRelations;
    }

    /**
     * @param array $data
     * @param array $skuProductIdRelations
     * @param array $emailCustomerIdRelations
     * @return array
     * @throws LocalizedException
     */
    protected function getCustomerPricesDataToSave($data, $skuProductIdRelations, $emailCustomerIdRelations)
    {
        $dataToSave               = [];

        foreach ($data as $datum) {

            $productId  = $this->getProductIdbySku($skuProductIdRelations, $datum['sku']);
            $customerId = $this->getCustomerIdByEmail($emailCustomerIdRelations, $datum['email']);

            if (is_null($customerId) || is_null($productId)) {
                continue;
            }

            $price        = $datum['price'];
            $specialPrice = $datum['special_price'];

            $attributeType = \MageWorx\CustomerPrices\Model\CustomerPrices::TYPE_CUSTOMER;

            $priceType        = $this->helperCalculate->getPriceType($price);
            $specialPriceType = $this->helperCalculate->getPriceType($specialPrice);

            $priceSign        = $this->helperCalculate->getPriceSign($price);
            $specialPriceSign = $this->helperCalculate->getPriceSign($specialPrice);

            $priceValue        = $this->helperCalculate->getPositivePriceValue($price);
            $specialPriceValue = $this->helperCalculate->getPositivePriceValue($specialPrice);

            $dataToSave[] = [
                'attribute_type'      => $attributeType,
                'customer_id'         => $customerId,
                'product_id'          => $productId,
                'price'               => $price,
                'special_price'       => $specialPrice,
                'price_type'          => $priceType,
                'special_price_type'  => $specialPriceType,
                'price_sign'          => $priceSign,
                'price_value'         => $priceValue,
                'special_price_sign'  => $specialPriceSign,
                'special_price_value' => $specialPriceValue
            ];
        }

        return $dataToSave;
    }

    /**
     * @param array $data
     * @param array $skuProductIdRelations
     * @param array $emailCustomerIdRelations
     * @return array
     * @throws LocalizedException
     */
    protected function getCustomerPricesDataToDelete($data, $skuProductIdRelations, $emailCustomerIdRelations)
    {
        /* Get collection From table mageworx_customerprices */
        $customerPricesCollection = $this->customerModel->getFullCustomersPricesData();
        $dataToDelete             = [];

        foreach ($data as $datum) {
            $productId  = $this->getProductIdbySku($skuProductIdRelations, $datum['sku']);
            $customerId = $this->getCustomerIdByEmail($emailCustomerIdRelations, $datum['email']);

            if (is_null($customerId) || is_null($productId)) {
                continue;
            }

            // check in table mageworx_customerprices by email and product id
            $hasRecord = $this->hasRecordInCustomPrices($customerPricesCollection, $productId, $customerId);
            if ($hasRecord) {
                $dataToDelete[] = ['product_id' => $productId, 'customer_id' => $customerId];
            }
        }

        return $dataToDelete;

    }

    /**
     * @param $rawData
     * @throws \Zend_Validate_Exception
     */
    protected function checkCorrectEmail($rawData)
    {
        $errorEmail = [];

        /* check correct email */
        foreach ($rawData as $dataIndex => $datum) {
            // skip headers
            if ($dataIndex == 0) {
                continue;
            }

            if (is_array($datum) && !\Zend_Validate::is(trim($datum[1]), 'EmailAddress')) {
                $errorEmail[] = $datum[1];
            }
        }

        if (!empty($errorEmail)) {
            throw new \Exception(__('Not correct email %1', $errorEmail));
        }
    }

    /**
     * @param array $rawData
     * @return array
     */
    protected function getDataFormCSV($rawData)
    {
        $prepareData = [];
        foreach ($rawData as $dataIndex => $datum) {
            // skip headers
            if ($dataIndex == 0) {
                continue;
            }

            if (is_array($datum) && count($datum) == 6) {
                $prepareData[] = [
                    self::ENTITY_ID     => $datum[0],
                    self::EMAIL         => $datum[1],
                    self::SKU           => $datum[2],
                    self::QTY           => $datum[3],
                    self::PRICE         => $datum[4],
                    self::SPECIAL_PRICE => $datum[5],
                ];
            }
        }

        return $prepareData;
    }

    /**
     * @param array $prepareData
     * @return array
     */
    protected function getListEmail($prepareData)
    {
        $listEmail = [];
        foreach ($prepareData as $data) {
            array_push($listEmail, $data['email']);
        }

        if (!empty($listEmail)) {
            array_unique($listEmail);
        }

        return $listEmail;
    }

    /**
     * @param array $prepareData
     * @return array
     */
    protected function getListSku($prepareData)
    {
        $listSku = [];
        foreach ($prepareData as $data) {
            array_push($listSku, $data['sku']);
        }

        if (!empty($listSku)) {
            array_unique($listSku);
        }

        return $listSku;
    }

    /**
     * Check record in table mageworx_customerPrices
     *
     * @param array $customerCollection
     * @param int $productId
     * @param int $customerId
     * @return boolean
     */
    protected function hasRecordInCustomPrices($customerCollection, $productId, $customerId)
    {
        foreach ($customerCollection as $datum) {
            if ($datum['product_id'] == $productId && $datum['customer_id'] == $customerId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $customerCollection
     * @param String $email
     * @return String|null
     */
    protected function getCustomerIdByEmail($customerCollection, $email)
    {
        if (!empty($customerCollection[$email])) {
            return $customerCollection[$email];
        }

        return null;
    }

    /**
     * @param array $productCollection
     * @param string $sku
     * @return mixed
     */
    protected function getProductIdbySku($productCollection, $sku)
    {
        if (!empty($productCollection[$sku])) {
            return $productCollection[$sku];
        }

        return null;
    }

    /**
     * @param array $saveCollection
     * @return array
     */
    protected function getIdsProducts($saveCollection)
    {
        $ids = [];
        foreach ($saveCollection as $datum) {
            $ids[] = $datum['product_id'];
        }

        $ids = array_unique($ids);

        return $ids;
    }

    /**
     * @return array
     */
    protected function getAllowedFormatFile()
    {
        return ['text/csv', 'application/vnd.ms-excel'];
    }
}