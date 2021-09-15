<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition;

class Address extends \Amasty\Segments\Model\Rule\Condition\Condition
{
    /**
     * @var string
     */
    protected $type = '';
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    private $directoryCountry;

    /**
     * @var \Magento\Directory\Model\Config\Source\Allregion
     */
    private $directoryAllregion;

    /**
     * use MainValidation trait
     */
    use \Amasty\Segments\Traits\MainValidation;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $directoryCountry
     * @param \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion
     * @param \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
     * @param \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->directoryCountry = $directoryCountry;
        $this->directoryAllregion = $directoryAllregion;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'postcode'   => __('Shipping Postcode'),
            'region_id'  => __('Shipping State/Province'),
            'city'       => __('Shipping City'),
            'country_id' => __('Shipping Country'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'postcode':
                return 'numeric';
            case 'country_id':
            case 'region_id':
                return 'select';
        }

        return 'string';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'country_id':
            case 'region_id':
                return 'select';
        }

        return 'text';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = $this->directoryCountry->toOptionArray();
                    break;

                case 'region_id':
                    $options = $this->directoryAllregion->toOptionArray();
                    break;

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }

        return $this->getData('value_select_options');
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        $operators = parent::getDefaultOperatorInputByType();
        $operators['string'] = ['==', '!=', '{}', '!{}'];
        $operators['numeric'] = ['==', '!=', '{}', '!{}'];

        return $operators;
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

        $address = $this->getAddress($customer);

        if ($address instanceof \Magento\Framework\Model\AbstractModel) {
            return parent::validate($address);
        }

        if (!$address && !$model->getCustomerIsGuest()) {
            /**
             * If customer doesn't have default address, then validate all addresses.
             * If one of the all addresses will be valid, then customer is valid.
             */
            $addressValidated = false;
            foreach ($model->getAddresses() as $address) {
                if (parent::validate($address)) {
                    return true;
                }
                $addressValidated = true;
            }
            if ($addressValidated) {
                return false;
            }
        }



        return $this->validateAttribute($address);
    }

    /**
     * Case and type insensitive comparison of values
     *
     * @param string|int|float $validatedValue
     * @param string|int|float $value
     * @param bool $strict
     * @return bool
     */
    protected function _compareValues($validatedValue, $value, $strict = true)
    {
        if ($strict && is_numeric($validatedValue) && is_numeric($value)) {
            return $validatedValue == $value;
        }
        if (stripos($value, $validatedValue) !== false) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function canValidateGuest()
    {
        return true;
    }

    /**
     * @param \Magento\Customer\Model\Customer|\Amasty\Segments\Model\GuestCustomerData $model
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function getAddress($model)
    {
        switch ($this->type) {
            case 'billing':
                return $model->getDefaultBillingAddress();
            case 'shipping':
                return $model->getDefaultShippingAddress();
        }

        return $model;
    }
}
