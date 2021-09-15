<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Plugin\Group;

use \Magento\Framework\App\Request\Http as HttpRequest;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices as CustomerGroupPricesResourceModel;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;

class SaveCustomerGroupPriceDataPlugin
{
    /**
     * Request object
     *
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var CustomerGroupPricesResourceModel
     */
    protected $customerGroupPricesResourceModel;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * SaveCustomerGroupPriceDataPlugin constructor.
     *
     * @param HttpRequest $request
     * @param CustomerGroupPricesResourceModel $customerGroupPricesResourceModel
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        HttpRequest $request,
        CustomerGroupPricesResourceModel $customerGroupPricesResourceModel,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        $this->request                          = $request;
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
        $this->indexerRegistry                  = $indexerRegistry;
    }

    /**
     * Plugin before
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $post = $this->request->getParams();

        if (array_key_exists('id', $post)) {
            if ($post['mageworx_group_price'] !== null) {
                $this->customerGroupPricesResourceModel->saveGroupPrice(
                    $post['id'],
                    $post['mageworx_group_price'],
                    $post['mageworx_group_price_type']
                );
            } else {
                $this->customerGroupPricesResourceModel->deleteByGroupId($post['id']);
            }

            /* reindex catalog_product_price */
            $this->indexerRegistry->get(Processor::INDEXER_ID)->invalidate();
        }
    }
}