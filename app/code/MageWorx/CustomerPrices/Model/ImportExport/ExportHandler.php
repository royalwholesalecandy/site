<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Model\ImportExport;

use MageWorx\CustomerPrices\Api\Data\CustomerPricesInterface;
use Magento\Framework\DataObject;
use MageWorx\CustomerPrices\Api\CustomerPricesRepositoryInterface;

class ExportHandler
{
    const ENTITY_ID     = 'customer_id';
    const EMAIL         = 'email';
    const SKU           = 'sku';
    const QTY           = 'qty';
    const PRICE         = 'price';
    const SPECIAL_PRICE = 'special_price';

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $dataObjectFactory;

    /**
     * @var CustomerPricesRepositoryInterface
     */
    protected $customerPricesRepository;

    /**
     * @var array|CustomerPricesInterface[]
     */
    protected $customerPrices;

    /**
     * ExportHandler constructor.
     *
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataObject\Factory $dataObjectFactory
     * @param CustomerPricesRepositoryInterface $customerPricesRepository
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\DataObject\Factory $dataObjectFactory,
        CustomerPricesRepositoryInterface $customerPricesRepository
    ) {
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->dataObjectFactory        = $dataObjectFactory;
        $this->customerPricesRepository = $customerPricesRepository;
    }

    /**
     * Get content as a CSV string
     *
     * @return string
     */
    public function getContent()
    {
        $headers  = $this->getHeaders();
        $template = $this->getStringCsvTemplate($headers);
        // Add header (titles)
        $content[]      = $headers->toString($template);
        $customerPrices = $this->getCustomerPrices();

        foreach ($customerPrices as $datum) {
            if (!$datum instanceof \Magento\Framework\DataObject) {
                continue;
            }
            $datum->addData(
                [
                    'customer_id'   => $datum->getCustomerId(),
                    'email'         => $datum->getEmail(),
                    'sku'           => $datum->getSku(),
                    'qty'           => '',
                    'price'         => $datum->getPrice(),
                    'special_price' => $datum->getSpecialPrice()
                ]
            );
            $content[] = $datum->toString($template);
        }

        $contentAsAString = implode("\n", $content);

        return $contentAsAString;
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
     * @param array $ids
     * @return CustomerPricesInterface[]
     */
    private function getCustomerPrices($ids = [])
    {
        if (empty($this->customerPrices)) {
            if (!empty($ids)) {
                $this->searchCriteriaBuilder->addFilter(
                    'entity_id',
                    $ids,
                    'in'
                );
            }
            $searchCriteria       = $this->searchCriteriaBuilder->create();
            $this->customerPrices = $this->customerPricesRepository
                ->getList($searchCriteria, true)
                ->getItems();
        }

        return $this->customerPrices;
    }

    /**
     * Get headers for the selected entities
     *
     * @return \Magento\Framework\DataObject
     */
    protected function getHeaders()
    {
        $dataFields = [
            static::ENTITY_ID     => __('User ID'),
            static::EMAIL         => __('Email'),
            static::SKU           => __('SKU'),
            static::QTY           => __('QTY'),
            static::PRICE         => __('User Price'),
            static::SPECIAL_PRICE => __('User Special Price')
        ];

        $dataObject = $this->dataObjectFactory->create($dataFields);

        return $dataObject;
    }
}