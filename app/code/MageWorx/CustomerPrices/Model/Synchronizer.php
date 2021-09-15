<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Model;

use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;
use MageWorx\CustomerPrices\Model\ResourceModel\Product\Indexer\CustomerPrice as IndexerCustomerPrice;
use Magento\Catalog\Model\ProductFactory;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;

class Synchronizer
{
    /**
     * @var ResourceCustomerPrices
     */
    protected $customerPricesResourceModel;

    /**
     * @var IndexerCustomerPrice
     */
    protected $indexer;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var HelperCalculate
     */
    protected $helperCalculate;

    /**
     * Synchronize constructor.
     *
     * @param ResourceCustomerPrices $customerPricesResourceModel
     * @param IndexerCustomerPrice $indexer
     * @param ProductFactory $productFactory
     * @param HelperCalculate $helperCalculate
     */
    public function __construct(
        ResourceCustomerPrices $customerPricesResourceModel,
        IndexerCustomerPrice $indexer,
        ProductFactory $productFactory,
        HelperCalculate $helperCalculate
    ) {
        $this->customerPricesResourceModel = $customerPricesResourceModel;
        $this->indexer                     = $indexer;
        $this->productFactory              = $productFactory;
        $this->helperCalculate             = $helperCalculate;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function synchronizeData()
    {
        $collection  = $this->customerPricesResourceModel->loadCustomerPricesCollection();
        $productIds  = $this->helperCalculate->getProductIds($collection);
        $customerIds = $this->helperCalculate->getCustomerIds($collection);

        /* delete not correct data in table precatalog_product_entity_decimal */
        $this->customerPricesResourceModel->deleteNotCorrectSpecialPrice();

        foreach ($productIds as $productId){
            /* set data in catalog_product_entity_decimal */
            if (!$this->customerPricesResourceModel->hasSpecialAttributeByProductId($productId)){
                $this->customerPricesResourceModel->addRowWithSpecialAttribute($productId);
            }
        }

        $this->indexer->cleanAllTableReindex();
        foreach ($productIds as $productId) {
            $strTypeId = $this->customerPricesResourceModel->getTypeId($productId);
            $this->indexer->setTypeId($strTypeId);
            $this->indexer->reindexEntityCustomer($productId, $customerIds);
        }

        return $this;
    }
}