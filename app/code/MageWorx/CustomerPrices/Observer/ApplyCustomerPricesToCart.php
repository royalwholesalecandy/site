<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use MageWorx\CustomerPrices\Helper\Customer as HelperCustomer;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;

class ApplyCustomerPricesToCart implements ObserverInterface
{
    /**
     * @var HelperCustomer
     */
    protected $helperCustomer;

    /**
     * @var HelperCalculate
     */
    protected $helperCalculate;

    /**
     * @var ResourceCustomerPrices
     */
    protected $customerPricesResourceModel;

    /**
     * ApplyCustomerPricesToCart constructor.
     *
     * @param HelperCustomer $helperCustomer
     * @param HelperCalculate $helperCalculate
     * @param ResourceCustomerPrices $customerPricesResourceModel
     */
    public function __construct(
        HelperCustomer $helperCustomer,
        HelperCalculate $helperCalculate,
        ResourceCustomerPrices $customerPricesResourceModel
    ) {
        $this->helperCustomer              = $helperCustomer;
        $this->helperCalculate             = $helperCalculate;
        $this->customerPricesResourceModel = $customerPricesResourceModel;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $item                    = $observer->getEvent()->getData('quote_item');
        $item                    = $item->getParentItem() ? $item->getParentItem() : $item;
        $rowId                   = $this->helperCalculate->getLinkField();
        $priceAttributeId        = $this->customerPricesResourceModel->getPriceAttributeId();
        $specialPriceAttributeId = $this->customerPricesResourceModel->getSpecialPriceAttributeId();
        $customerId              = $this->helperCustomer->getCurrentCustomerId();

        if (!$customerId && !$item->getProductId()) {
            return $this;
        }

        if ($customerId !== null) {
            $customerProductPrices = $this->customerPricesResourceModel->getCalculatedProductDataByCustomer(
                $item->getProductId(),
                $customerId
            );
        }

        if (empty($customerProductPrices)) {
            return $this;
        }

        foreach ($customerProductPrices as $productPrice) {
            if (array_key_exists($rowId, $productPrice) && $item->getProductId() != $productPrice[$rowId]) {
                continue;
            }
            if (isset($productPrice['value']) && $productPrice['value'] >= 0) {
                if ($priceAttributeId == $productPrice['attribute_id']) {
                    $item->setCustomPrice($productPrice['value']);
                    $item->setOriginalCustomPrice($productPrice['value']);
                }

                if ($specialPriceAttributeId == $productPrice['attribute_id']) {
                    $item->setCustomPrice($productPrice['value']);
                    $item->setOriginalCustomPrice($productPrice['value']);
                }
            }
        }

        return $this;
    }
}