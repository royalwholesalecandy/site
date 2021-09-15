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
use Magento\Framework\EntityManager\EventManager;

class ProductIds implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperCalculate
     */
    protected $helperCalculate;

    /**
     * @var HelperCustomer
     */
    protected $helperCustomer;

    /**
     * @var ResourceCustomerPrices
     */
    protected $customerPricesResourceModel;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * ApplyCustomerPricesToProduct constructor.
     *
     * @param HelperData $helperData
     * @param HelperCalculate $helperCalculate
     * @param HelperCustomer $helperCustomer
     * @param ResourceCustomerPrices $customerPricesResourceModel
     * @param EventManager $eventManager
     */
    public function __construct(
        HelperData $helperData,
        HelperCalculate $helperCalculate,
        HelperCustomer $helperCustomer,
        ResourceCustomerPrices $customerPricesResourceModel,
        EventManager $eventManager
    ) {
        $this->helperData                  = $helperData;
        $this->helperCalculate             = $helperCalculate;
        $this->helperCustomer              = $helperCustomer;
        $this->customerPricesResourceModel = $customerPricesResourceModel;
        $this->eventManager                = $eventManager;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $object = $observer->getObject();
        $customerId = $this->helperCustomer->getCurrentCustomerId();

        if ($customerId !== null) {
            $collection = $this->customerPricesResourceModel->getDataByCustomerId($customerId);
            $productIds = $this->helperCalculate->getProductIds($collection);
            $object->addData(['product_ids' => $productIds]);
        }

        return $object;
    }

}