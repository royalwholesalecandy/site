<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use MageWorx\CustomerPrices\Helper\Data as HelperData;
use MageWorx\CustomerPrices\Helper\Customer as HelperCustomer;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;
use Magento\Catalog\Model\Product;

class ApplyCustomerPricesToProduct implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

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
     * ApplyCustomerPricesToProduct constructor.
     *
     * @param HelperData $helperData
     * @param HelperCustomer $helperCustomer
     * @param HelperCalculate $helperCalculate
     * @param ResourceCustomerPrices $customerPricesResourceModel
     */
    public function __construct(
        HelperData $helperData,
        HelperCustomer $helperCustomer,
        HelperCalculate $helperCalculate,
        ResourceCustomerPrices $customerPricesResourceModel
    ) {
        $this->helperData                  = $helperData;
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
        $product                 = $observer->getEvent()->getProduct();
        $rowId                   = $this->helperCalculate->getLinkField();
        $priceAttributeId        = $this->customerPricesResourceModel->getPriceAttributeId();
        $specialPriceAttributeId = $this->customerPricesResourceModel->getSpecialPriceAttributeId();
        $customerId              = $this->helperCustomer->getCurrentCustomerId();

        if (!$customerId || !$product instanceof Product) {
            return $this;
        }

        if ($customerId !== null) {
            $customerProductPrices = $this->customerPricesResourceModel->getCalculatedProductDataByCustomer(
                $product->getId(),
                $customerId
            );
        }

        if (empty($customerProductPrices)) {
            return $this;
        }

        foreach ($customerProductPrices as $productPrice) {
            if (array_key_exists($rowId, $productPrice) && $product->getId() != $productPrice[$rowId]) {
                continue;
            }
            if (isset($productPrice['value']) && $productPrice['value'] >= 0) {
                if ($priceAttributeId == $productPrice['attribute_id']) {
                    $product->setData('price', $productPrice['value']);
                }

                if ($specialPriceAttributeId == $productPrice['attribute_id']) {
                    $product->setData('special_price', $productPrice['value']);
                }
            }
        }


        return $this;
    }

}