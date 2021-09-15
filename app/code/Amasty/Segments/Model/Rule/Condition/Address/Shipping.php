<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition\Address;

class Shipping extends \Amasty\Segments\Model\Rule\Condition\Address
{
    /**
     * @var string
     */
    protected $type = 'shipping';

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'postcode'   => __('Shipping Zip'),
            'region_id'  => __('Shipping State/Province'),
            'city'       => __('Shipping City'),
            'country_id' => __('Shipping Country'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
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
        return $model->getDefaultShippingAddress();
    }
}
