<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition;

use Amasty\Segments\Model\Rule\Condition\Customer\NewsletterStatusOptionsProvider;

class Customer extends \Amasty\Segments\Model\Rule\Condition\Condition
{
    /**
     * use Traits
     */
    use \Amasty\Segments\Traits\MainValidation, \Amasty\Segments\Traits\DayValidation;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var array
     */
    protected $allowedAttributes = [
        'website_id', 'store_id', 'created_at', 'created_in', 'group_id', 'dob', 'disable_auto_group_change', 'email',
        'firstname', 'gender', 'confirmation', 'lastname', 'middlename', 'prefix', 'suffix', 'taxvat'
    ];

    /**
     * @var array
     */
    protected $specialAttributes;

    /**
     * @var NewsletterStatusOptionsProvider
     */
    protected $newsletterOptionsProvider;

    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $subscriber;

    /**
     * @var \Amasty\Segments\Helper\Customer\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\Logger
     */
    protected $customerLogger;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    private $yesnoOptions;

    /**
     * Customer constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context                                             $context
     * @param \Magento\Customer\Model\ResourceModel\Customer                                    $customerResource
     * @param \Magento\Customer\Model\CustomerFactory                                           $customerFactory
     * @param NewsletterStatusOptionsProvider                                                   $newsletterOptionsProvider
     * @param \Magento\Newsletter\Model\Subscriber | \Magento\Newsletter\Model\Subscriber\Proxy $subscriber
     * @param \Amasty\Segments\Helper\Customer\Data                                             $helper
     * @param array                                                                             $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        NewsletterStatusOptionsProvider $newsletterOptionsProvider,
        \Magento\Newsletter\Model\Subscriber\Proxy $subscriber,
        \Amasty\Segments\Helper\Customer\Data $helper,
        \Magento\Customer\Model\Logger $customerLogger,
        \Magento\Config\Model\Config\Source\Yesno $yesnoOptions,
        array $data = []
    ) {
        $this->customerResource          = $customerResource;
        $this->customerFactory           = $customerFactory;
        $this->newsletterOptionsProvider = $newsletterOptionsProvider;
        $this->subscriber                = $subscriber;
        $this->helper                    = $helper;
        $this->customerLogger            = $customerLogger;
        $this->yesnoOptions = $yesnoOptions;
        $this->specialAttributes =  [
            'is_newsletter_subscribe' => __('Is Newsletter Subscriber'),
            'day_from_last_visit'     => __('Days From the Last Visit'),
            'day_from_registration'   => __('Days From the Registration'),
            'day_before_birthday'     => __('Days Before Birthday')
        ];

        parent::__construct($context, $data);
    }

    /**
     * Retrieve attribute object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getAttributeObject()
    {
        return $this->customerResource->getAttribute($this->getAttribute());
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $customerAttributes = $this->customerResource
            ->loadAllAttributes()
            ->getAttributesByCode();

        /**
         * validation by setting set attributes
         */
        $selectedAttrInConfig = $this->helper->getConfigValueByPath(
            \Amasty\Segments\Helper\Base::CONFIG_PATH_GENERAL_CUSTOMER_ATTRIBUTES
        );

        $attributes = [];

        if ($selectedAttrInConfig) {
            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
            foreach ($customerAttributes as $attribute) {

                if (!($attribute->getFrontendLabel()) || !($attribute->getAttributeCode())) {
                    continue;
                }

                if (in_array($attribute->getAttributeCode(), explode(',', $selectedAttrInConfig))) {
                    $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
                }
            }
        }

        $this->_addSpecialAttributes($attributes);
        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @param array $attributes
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes = array_merge_recursive($attributes, $this->specialAttributes);
    }

    /**
     * @return $this
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * This value will define which operators will be available for this condition.
     *
     * Possible values are: string, numeric, date, select, multiselect, grid, boolean
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'day_from_last_visit':
            case 'day_from_registration':
            case 'day_before_birthday':
                return 'day';
            case 'is_newsletter_subscribe':
                return 'select';
        }

        $customerAttribute = $this->getAttributeObject();

        if (!$customerAttribute) {
            return parent::getInputType();
        }

        return $this->getInputTypeFromAttribute($customerAttribute);
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $customerAttribute
     *
     * @return string
     */
    protected function getInputTypeFromAttribute($customerAttribute)
    {
        if (!is_object($customerAttribute)) {
            $customerAttribute = $this->getAttributeObject();
        }

        $possibleTypes = ['string', 'numeric', 'date', 'select', 'multiselect', 'grid', 'boolean'];
        if (in_array($customerAttribute->getFrontendInput(), $possibleTypes)) {
            return $customerAttribute->getFrontendInput();
        }

        switch ($customerAttribute->getFrontendInput()) {
            case 'gallery':
            case 'media_image':
            case 'selectimg':
            case 'radios':
                return 'select';
            case 'multiselectimg':
            case 'checkboxes':
                return 'multiselect';
        }

        return 'string';
    }

    /**
     * @return $this|string
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();

        switch ($this->getInputType()) {
            case 'date':
                $element->setClass('hasDatepicker');
                break;
        }

        return $element;
    }

    /**
     * @return bool
     */
    public function getExplicitApply()
    {
        return ($this->getInputType() == 'date');
    }

    /**
     * Value element type will define renderer for condition value element
     *
     * @see \Magento\Framework\Data\Form\Element
     * @return string
     */
    public function getValueElementType()
    {
        $customerAttribute = $this->getAttributeObject();

        if ($this->getAttribute() == 'is_newsletter_subscribe') {
            return 'select';
        }

        if (!is_object($customerAttribute)) {
            return parent::getValueElementType();
        }

        $availableTypes = [
            'checkbox',
            'date',
            'editablemultiselect',
            'editor',
            'fieldset',
            'file',
            'gallery',
            'image',
            'imagefile',
            'multiline',
            'multiselect',
            'radio',
            'select',
            'text',
            'textarea',
            'time'
        ];

        if (in_array($customerAttribute->getFrontendInput(), $availableTypes)) {
            return $customerAttribute->getFrontendInput();
        }

        switch ($customerAttribute->getFrontendInput()) {
            case 'selectimg':
            case 'radios':
            case 'boolean':
                return 'select';
            case 'multiselectimg':
            case 'checkboxes':
                return 'multiselect';
        }

        return parent::getValueElementType();
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        $selectOptions = [];
        $attributeObject = $this->getAttributeObject();

        if (is_object($attributeObject) && $attributeObject->usesSource()) {
            $addEmptyOption = true;
            if ($attributeObject->getFrontendInput() == 'multiselect') {
                $addEmptyOption = false;
            }
            $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
        }

        if ($this->getInputType() == 'boolean' && count($selectOptions) == 0) {
            $selectOptions = $this->yesnoOptions->toOptionArray();
        }

        if ($this->getAttribute() == 'is_newsletter_subscribe') {
            $selectOptions = $this->newsletterOptionsProvider->toOptionArray();
        }

        $key = 'value_select_options';

        if (!$this->hasData($key)) {
            $this->setData($key, $selectOptions);
        }

        return $this->getData($key);
    }

    /**
     * Return real Customer attribute code for validate
     *
     * @return string
     */
    protected function getEavAttributeCode()
    {
        switch ($this->getAttribute()) {
            case 'day_from_last_visit':
                return 'last_visit_at';
            case 'day_from_registration':
                return 'created_at';
            case 'day_before_birthday':
                return 'dob';
        }

        return $this->getAttribute();
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     *
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $customer = $this->objectValidation($model);

        if (!$customer) {
            return false;
        }

        if (array_key_exists($this->getAttribute(), $this->specialAttributes)) {
            return $this->validationByAttribute($customer);
        }

        if (!$customer instanceof \Magento\Customer\Model\Customer) {
            $customer = $model->getQuote()->getCustomer();
            $attr     = $this->getAttribute();

            $allAttr = $customer->toArray();

            if ($attr != 'entity_id' && !array_key_exists($attr, $allAttr)) {
                $address        = $model->getQuote()->getBillingAddress();
                $allAttr[$attr] = $address->getData($attr);
            }
            $customer = $this->customerFactory->create()->setData($allAttr);
        }

        return parent::validate($customer);
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return bool
     */
    public function validationByAttribute($customer)
    {
        if ($this->getAttribute() && $customer->getId()) {
            switch ($this->getAttribute()) {
                case 'day_from_last_visit':
                    $lastLoggedAt = $this->customerLogger->get($customer->getId());
                    if (!$lastLoggedAt->getLastVisitAt()) {
                        return false;
                    }
                    $customer->setData($this->getEavAttributeCode(), $lastLoggedAt->getLastVisitAt());
                // no break
                case 'day_from_registration':
                case 'day_before_birthday':
                    $attributeValue = $this->prepareDayValidation($customer);

                    return $this->validateAttribute($attributeValue);
                case 'is_newsletter_subscribe':
                    $subscriber = $this->subscriber->loadByCustomerId($customer->getId());

                    if ($subscriber->getData()) {
                        return $this->validateAttribute($subscriber->getStatus());
                    }
                    return false;

                default:
                    break;
            }
        }

        return false;
    }
}
