<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use MageWorx\CustomerPrices\Helper\Data as HelperData;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use MageWorx\CustomerPrices\Helper\Customer as HelperCustomer;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;
use Magento\Framework\App\State;

/**
 * Class ApplyCustomerPricesToCollection
 *
 * @package MageWorx\CustomerPrices\Observer
 */
class ApplyCustomerPricesToCollection implements ObserverInterface
{
    /**
     * @var HelperCalculate
     */
    protected $helperCalculate;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperCustomer
     */
    protected $helperCustomer;

    /**
     * @var ResourceCustomerPrices
     */
    protected $customerPricesResourceModel;

    /**
     * @var State
     */
    protected $appState;

    /**
     * ApplyCustomerPricesToCollection constructor.
     *
     * @param HelperCalculate $helperCalculate
     * @param HelperData $helperData
     * @param HelperCustomer $helperCustomer
     * @param ResourceCustomerPrices $customerPricesResourceModel
     * @param State $appState
     */
    public function __construct(
        HelperCalculate $helperCalculate,
        HelperData $helperData,
        HelperCustomer $helperCustomer,
        ResourceCustomerPrices $customerPricesResourceModel,
        State $appState
    ) {
        $this->helperCalculate             = $helperCalculate;
        $this->helperData                  = $helperData;
        $this->helperCustomer              = $helperCustomer;
        $this->customerPricesResourceModel = $customerPricesResourceModel;
        $this->appState                    = $appState;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        if ($this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return $this;
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection              = $observer->getData('collection');
        $rowId                   = $this->helperCalculate->getLinkField();
        $customerId              = $this->helperCustomer->getCurrentCustomerId();
        $priceAttributeId        = $this->customerPricesResourceModel->getPriceAttributeId();
        $specialPriceAttributeId = $this->customerPricesResourceModel->getSpecialPriceAttributeId();
        
        if (!$this->helperCalculate->isCheckedCollection($customerId, $collection)) {
            return $this;
        }

        $ids = $this->helperCalculate->getIds($collection);
        if (!empty($ids) && $customerId !== null) {
            $customerProductPrices = $this->customerPricesResourceModel->getCalculatedProductsDataByCustomer(
                $ids,
                $customerId
            );
        }

        if (empty($customerProductPrices)) {
            return $this;
        }

        foreach ($collection as $product) {
            foreach ($customerProductPrices as $productPrice) {
                if (array_key_exists($rowId, $productPrice) && $product->getId() != $productPrice[$rowId]) {
                    continue;
                }
                if (isset($productPrice['value']) && $productPrice['value'] >= 0) {
                    if ($priceAttributeId == $productPrice['attribute_id']) {
                        $product['price'] = $productPrice['value'];
                    }

                    if ($specialPriceAttributeId == $productPrice['attribute_id']) {
                        $product->setData('special_price', $productPrice['value']);
                    }
                }
            }
        }

        return $this;
    }

}