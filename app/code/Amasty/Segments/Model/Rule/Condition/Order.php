<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition;

use \Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;

class Order extends \Amasty\Segments\Model\Rule\Condition\Condition
{
    /**
     * use traits
     */
    use \Amasty\Segments\Traits\MainValidation, \Amasty\Segments\Traits\DayValidation;

    /**
     * @var \Magento\Shipping\Model\Config\Source\Allmethods
     */
    protected $shippingAllmethods;

    /**
     * @var \Magento\Payment\Model\Config\Source\Allmethods
     */
    protected $paymentAllmethods;

    /**
     * @var \Amasty\Segments\Helper\Order\Data
     */
    protected $helper;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    private $yesnoOptions;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
     * @param \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods
     * @param \Amasty\Segments\Helper\Order\Data $orderHelper
     * @param \Magento\Config\Model\Config\Source\Yesno $yesnoOptions
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Shipping\Model\Config\Source\Allmethods\Proxy $shippingAllmethods,
        \Magento\Payment\Model\Config\Source\Allmethods\Proxy $paymentAllmethods,
        \Amasty\Segments\Helper\Order\Data $orderHelper,
        \Magento\Config\Model\Config\Source\Yesno $yesnoOptions,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->shippingAllmethods = $shippingAllmethods;
        $this->paymentAllmethods  = $paymentAllmethods;
        $this->helper             = $orderHelper;
        parent::__construct($context, $data);
        $this->yesnoOptions = $yesnoOptions;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieve attribute object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getAttributeObject()
    {
        if ($this->moduleManager->isEnabled('Amasty_Orderattr')) {
            return $this->helper->getOrderAttribute($this->getAttribute());
        }
        return null;
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'days_first_completed' => __('Days From First Completed Order'),
            'days_last_completed'  => __('Days From Last Completed Order'),
            'payment_method'       => __('Used Payment Methods'),
            'shipping_method'      => __('Used Shipping Methods'),
        ];
        /**
         * validation by setting set attributes
         */
        $selectedAttrInConfig = $this->helper->getConfigValueByPath(
            \Amasty\Segments\Helper\Base::CONFIG_PATH_GENERAL_ORDER_ATTRIBUTES
        );

        if ($selectedAttrInConfig) {
            $orderAttributes = $this->helper->getOrderAttributesForSource();
            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
            foreach ($orderAttributes as $attribute) {

                if (!($attribute->getFrontendLabel()) || !($attribute->getAttributeCode())) {
                    continue;
                }

                if (in_array($attribute->getAttributeCode(), explode(',', $selectedAttrInConfig))) {
                    $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
                }
            }
        }

        asort($attributes);

        $this->setAttributeOption($attributes);

        return $this;
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
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'days_first_completed':
            case 'days_last_completed':
                return 'day';

            case 'shipping_method':
            case 'payment_method':
                return 'select';
        }

        $orderAttribute = $this->getAttributeObject();

        if (!$orderAttribute) {
            return parent::getInputType();
        }

        return $this->getInputTypeFromAttribute($orderAttribute);
    }

    /**
     * @param mixed $orderAttribute
     * @return mixed|string
     */
    protected function getInputTypeFromAttribute($orderAttribute)
    {
        if (!is_object($orderAttribute)) {
            $orderAttribute = $this->getAttributeObject();
        }

        $possibleTypes = ['string', 'numeric', 'date', 'select', 'multiselect', 'grid', 'boolean'];
        if (in_array($orderAttribute->getFrontendInput(), $possibleTypes)) {
            return $orderAttribute->getFrontendInput();
        }

        switch ($orderAttribute->getFrontendInput()) {
            case 'gallery':
            case 'media_image':
            case 'radios':
            case 'selectimg':
                return 'select';
            case 'multiselectimg':
            case 'checkboxes':
                return 'multiselect';
        }

        return 'string';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'shipping_method':
            case 'payment_method':
                return 'select';
        }
        $orderAttribute = $this->getAttributeObject();
        if (!is_object($orderAttribute)) {
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

        if (in_array($orderAttribute->getFrontendInput(), $availableTypes)) {
            return $orderAttribute->getFrontendInput();
        }

        switch ($orderAttribute->getFrontendInput()) {
            case 'radios':
            case 'selectimg':
            case 'boolean':
            case 'radios':
                return 'select';
            case 'multiselectimg':
            case 'checkboxes':
                return 'multiselect';
        }

        return parent::getValueElementType();
    }

    /**
     * @return mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {

            switch ($this->getAttribute()) {
                case 'shipping_method':
                    $options = $this->shippingAllmethods->toOptionArray();
                    break;

                case 'payment_method':
                    $options = $this->paymentAllmethods->toOptionArray();
                    break;

                default:
                    $options = [];
                    $attributeObject = $this->getAttributeObject();

                    if (is_object($attributeObject) && $attributeObject->usesSource()) {
                        $addEmptyOption = true;
                        if ($attributeObject->getFrontendInput() == 'multiselect') {
                            $addEmptyOption = false;
                        }
                        $options = $attributeObject->getSource()->getAllOptions($addEmptyOption);
                    }

                    if ($this->getInputType() == 'boolean' && count($options) == 0) {
                        $options = $this->yesnoOptions->toOptionArray();
                    }
                    break;
            }
            $this->setData('value_select_options', $options);
        }

        return $this->getData('value_select_options');
    }

    /**
     * Return real Order attribute for validate
     *
     * @return string
     */
    protected function getEavAttributeCode()
    {
        switch ($this->getAttribute()) {
            case 'days_first_completed':
            case 'days_last_completed':
                return 'updated_at';
        }

        return $this->getAttribute();
    }

    /**
     * @param \Magento\Customer\Model\Customer|\Amasty\Segments\Model\GuestCustomerData $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $model = $this->objectValidation($model);
        $orderCollection = $this->helper->getCollectionByCustomerType($model);

        if (!$orderCollection) {
            return false;
        }
        $attribute = $this->getAttribute();
        switch ($attribute) {
            case 'payment_method':
            case 'shipping_method':
                return $this->validateOrderByMethods($orderCollection);
            case 'days_first_completed':
            case 'days_last_completed':
                return $this->validateOrderByAttribute($orderCollection);
        }
        if ($this->moduleManager->isEnabled('Amasty_Orderattr')) {
            $orderCollection->addFieldToFilter('state', ['eq' => \Magento\Sales\Model\Order::STATE_COMPLETE]);
            foreach ($orderCollection->getItems() as $order) {
                try {
                    $orderAttributeData = $this->objectManager
                        ->create('\Amasty\Orderattr\Model\Order\Attribute\Value')
                        ->loadByOrderId($order->getId());
                } catch (\ReflectionException $e) {
                    $orderAttributeData = $this->objectManager
                        ->get('\Amasty\Orderattr\Model\Entity\EntityResolver')
                        ->getEntityByOrder($order);
                }
                if (parent::validateAttribute(
                    $orderAttributeData->getData($attribute)
                )) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param OrderCollection $orderCollection
     *
     * @return bool
     */
    private function validateOrderByMethods($orderCollection)
    {
        /** @var \Magento\Sales\Model\Order $order */
        foreach ($orderCollection->getItems() as $order) {
            if ('payment_method' == $this->getAttribute()) {
                $value = $order->getPayment()->getMethodInstance()->getCode();

                $order->setData($this->getAttribute(), $value);
            }

            if (parent::validate($order)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param OrderCollection $orderCollection
     *
     * @return bool
     */
    private function validateOrderByAttribute($orderCollection)
    {
        $orderCollection->addFieldToFilter('state', ['eq' => \Magento\Sales\Model\Order::STATE_COMPLETE]);

        /** @var \Magento\Sales\Model\Order $order */
        switch ($this->getAttribute()) {
            case 'days_first_completed':
                $orderCollection->setOrder('updated_at', OrderCollection::SORT_ORDER_DESC);
                break;
            case 'days_last_completed':
                $orderCollection->setOrder('updated_at', OrderCollection::SORT_ORDER_ASC);
                break;
        }

        $order = $orderCollection->setPage(1, 1)->getFirstItem();

        $attributeValue = $this->prepareDayValidation($order);

        return parent::validateAttribute($attributeValue);
    }

    /**
     * {@inheritdoc}
     * @since 1.1.1 allowed for guests
     */
    protected function canValidateGuest()
    {
        return true;
    }
}
