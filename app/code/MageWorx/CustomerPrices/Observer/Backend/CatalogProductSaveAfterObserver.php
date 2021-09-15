<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use MageWorx\CustomerPrices\Helper\Data as HelperData;
use MageWorx\CustomerPrices\Helper\Customer as HelperCustomer;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;
use MageWorx\CustomerPrices\Model\ResourceModel\Product\Indexer\CustomerPrice as IndexCustomPrice;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\Framework\Indexer\IndexerRegistry;

class CatalogProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var HelperCustomer
     */
    private $helperCustomer;

    /**
     * @var ResourceCustomerPrices
     */
    private $customerPricesResourceModel;

    /**
     * @var IndexCustomPrice
     */
    private $indexer;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * CatalogProductSaveAfterObserver constructor.
     *
     * @param HelperData $helperData
     * @param HelperCustomer $helperCustomer
     * @param ResourceCustomerPrices $customerPricesResourceModel
     * @param IndexCustomPrice $indexer
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        HelperData $helperData,
        HelperCustomer $helperCustomer,
        ResourceCustomerPrices $customerPricesResourceModel,
        IndexCustomPrice $indexer,
        IndexerRegistry $indexerRegistry
    ) {
        $this->helperData                  = $helperData;
        $this->helperCustomer              = $helperCustomer;
        $this->customerPricesResourceModel = $customerPricesResourceModel;
        $this->indexer                     = $indexer;
        $this->indexerRegistry             = $indexerRegistry;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (!$product->getId()) {
            return $this;
        }

        $customerIds   = $this->getCustomerIds($product);
        $productTypeId = $product->getTypeId();
        if (!empty($productTypeId) && !empty($customerIds)) {

            /* set data in catalog_product_entity_decimal */
            if (!$this->customerPricesResourceModel->hasSpecialAttributeByProductId($product->getId())) {
                $this->customerPricesResourceModel->addRowWithSpecialAttribute($product->getId());
            }

            /* reindex data */
            $this->indexer->setTypeId($productTypeId);
            $this->indexer->reindexEntityCustomer($product->getId(), $customerIds);

            /* add notification need reindex catalogrule_rule */
            if ($this->helperData->isEnabledCustomerPriceInCatalogPriceRule()) {
                $this->indexerRegistry->get(RuleProductProcessor::INDEXER_ID)->invalidate();

            }
        }

        return $this;
    }

    /**
     * @param $product
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCustomerIds($product)
    {
        $data = $this->customerPricesResourceModel->getDataByProductId($product->getId());

        $ids = [];
        foreach ($data as $elem) {
            $ids[] = $elem['customer_id'];
        }

        $ids = array_unique($ids);

        return $ids;
    }
}
