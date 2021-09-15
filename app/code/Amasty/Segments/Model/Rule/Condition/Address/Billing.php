<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition\Address;

class Billing extends \Amasty\Segments\Model\Rule\Condition\Address
{
    /**
     * @var string
     */
    protected $type = 'billing';

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'email'      => __('Email'),
            'city'       => __('Billing City'),
            'region_id'  => __('Billing State/Province'),
            'country_id' => __('Billing Country'),
            'postcode'   => __('Billing Zip'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Customer\Model\Customer|\Amasty\Segments\Model\GuestCustomerData $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $customer = $this->objectValidation($model);
        if (!$customer) {
            return false;
        }

        if ('email' == $this->getAttribute()) {
            return $this->validateAttribute($model->getEmail());
        }

        return parent::validate($model);
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Customer\Model\Customer|\Amasty\Segments\Model\GuestCustomerData $model
     *
     * @return \Magento\Customer\Model\Address
     */
    protected function getAddress($model)
    {
        return $model->getDefaultBillingAddress();
    }
}
