<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Traits;

/**
 * Used in Conditions.
 * Guest Validation available for Condition types: Billing Address, Shipping Address, Cart, Order
 * @since 1.1.1 added Order conditions for guest
 */
trait MainValidation
{
    /**
     * @var string
     */
    protected $explodeDelimiter = '\\';

    /**
     * @param \Magento\Customer\Model\Customer|\Amasty\Segments\Model\GuestCustomerData $object
     * @return bool|\Magento\Customer\Model\Customer|\Amasty\Segments\Model\GuestCustomerData
     */
    public function objectValidation($object)
    {
        if ($object instanceof \Magento\Customer\Model\Customer) {
            return $object;
        }

        if ($object instanceof \Amasty\Segments\Model\GuestCustomerData && $this->canValidateGuest()) {
            return $object;
        }

        return false;
    }

    /**
     * Is this condition can be used for Guest
     *
     * @return bool
     */
    protected function canValidateGuest()
    {
        return false;
    }
}
