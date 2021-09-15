<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model;

/**
 * Shoul be extended from AbstractModel @see \Magento\Rule\Model\Condition\Combine::validate
 * @method int getWebsiteId() getWebsiteId()
 * @method GuestCustomerData setWebsiteId($value)
 * @method int getStoreId() getStoreId()
 * @method \Magento\Framework\Model\AbstractModel getQuote()
 * @method string|integer getCustomerIsGuest() getCustomerIsGuest()
 * @method GuestCustomerData setGroupId($value)
 * @method GuestCustomerData setDefaultBillingAddress($value)
 * @method GuestCustomerData setDefaultShippingAddress($value)
 * @method GuestCustomerData setCustomerIsGuest($value)
 * @method GuestCustomerData setQuote($value)
 * @method GuestCustomerData setCreatedAt($value)
 */
class GuestCustomerData extends \Magento\Framework\Model\AbstractModel
{
    protected $_idFieldName = 'quote_id';

    /**
     * @return string|null
     */
    public function getEmail()
    {
        if ($this->hasData('email')) {
            return $this->_getData('email');
        }
        return $this->_getData('customer_email');
    }
}
