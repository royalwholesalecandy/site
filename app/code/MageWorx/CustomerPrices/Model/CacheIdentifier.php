<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Model;

use MageWorx\CustomerPrices\Helper\Customer as HelperCustomer;
use \MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;

class CacheIdentifier
{
    /**
     * @var HelperCustomer
     */
    protected $helperCustomer;

    /**
     * @var ResourceCustomerPrices
     */
    protected $resourceCustomerPrices;

    /**
     * @var int|null
     */
    protected $customerId;

    /**
     * @var int|null
     */
    protected $customerIdWithPrice = null;

    /**
     * CacheIdentifier constructor.
     *
     * @param HelperCustomer $helperCustomer
     * @param ResourceCustomerPrices $resourceCustomerPrices
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        HelperCustomer $helperCustomer,
        ResourceCustomerPrices $resourceCustomerPrices
    ) {
        $this->helperCustomer         = $helperCustomer;
        $this->resourceCustomerPrices = $resourceCustomerPrices;
        $this->customerId             = $this->helperCustomer->getCurrentCustomerId();
    }

    /**
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addCustomerDate($result)
    {
        if (is_null($this->customerIdWithPrice)) {
            if (!$this->customerId) {
                return $result;
            }
            if ($this->resourceCustomerPrices->hasAssignCustomer($this->customerId)) {
                $this->customerIdWithPrice = $this->customerId;
            }
        }

        if (!empty($result) && !is_null($this->customerIdWithPrice)) {
            $result['mageworx_customer_id'] = $this->customerId;
        }

        return $result;
    }
}