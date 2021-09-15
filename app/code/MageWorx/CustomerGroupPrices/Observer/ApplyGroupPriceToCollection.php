<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use MageWorx\CustomerGroupPrices\Helper\Group as HelperGroup;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices as CustomerGroupPricesResourceModel;
use Magento\Framework\App\State;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\DataObject;

class ApplyGroupPriceToCollection implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperGroup
     */
    protected $helperGroup;

    /**
     * @var CustomerGroupPricesResourceModel
     */
    protected $customerGroupPricesResourceModel;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var array
     */
    protected $productIds;

    /**
     * @param HelperData $helperData
     * @param HelperGroup $helperGroup
     * @param CustomerGroupPricesResourceModel $customerGroupPricesResourceModel
     * @param State $appState
     * @param EventManager $eventManager
     */
    public function __construct(
        HelperData $helperData,
        HelperGroup $helperGroup,
        CustomerGroupPricesResourceModel $customerGroupPricesResourceModel,
        State $appState,
        EventManager $eventManager
    ) {
        $this->helperData                       = $helperData;
        $this->helperGroup                      = $helperGroup;
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
        $this->appState                         = $appState;
        $this->eventManager                     = $eventManager;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->helperData->isEnabledCustomerGroupPrice()) {
            return $this;
        }

        if ($this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return $this;
        }

        /**@var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $observer->getData('collection');
        $rowId      = $this->helperData->getLinkField();
        $ids        = $collection->getAllIds();

        if (!empty($ids)) {
            $customerGroupId = $this->helperGroup->getCurrentCustomerGroupId();
            $resultSQL       = $this->customerGroupPricesResourceModel->getDataFromDecimalTempTable(
                $ids,
                $customerGroupId
            );
        }

        if (empty($resultSQL)) {
            return $this;
        }

        $priceAttributeId        = $this->customerGroupPricesResourceModel->getPriceAttributeId();
        $specialPriceAttributeId = $this->customerGroupPricesResourceModel->getSpecialPriceAttributeId();
        $data                    = new DataObject();
        $data->setProductIds([]);
        $this->eventManager->dispatch('mageworx_customerprices_product_ids', ['object' => $data]);
        $this->productIds = $data->getProductIds();

        foreach ($collection as $product) {
            /* compatibility with mageworx_customerprices */
            if (!empty($this->productIds)) {
                if (in_array($product->getId(), $this->productIds)) {
                    return $this;
                }
            }
            foreach ($resultSQL as $productPrice) {
                if ($product->getId() != $productPrice[$rowId]) {
                    continue;
                }
                if (isset($productPrice['value']) && $productPrice['value'] >= 0) {
                    if ($priceAttributeId == $productPrice['attribute_id']) {
                        $product['price'] = $productPrice['value'];
                    }

                    if ($specialPriceAttributeId == $productPrice['attribute_id']
                        && $product->getPrice() > $productPrice['value']) {
                        $product->setData('special_price', $productPrice['value']);
                    }
                }
            }
        }

        unset($ids);

        return $this;
    }
}