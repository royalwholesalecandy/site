<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition\Order;

/**
 * Product rule condition data model
 */
class Common extends \Amasty\Segments\Model\Rule\Condition\Condition
{
    /**
     * use traits
     */
    use \Amasty\Segments\Traits\MainValidation, \Amasty\Segments\Traits\DayValidation;

    /**
     * @var \Magento\Sales\Model\Config\Source\Order\Status
     */
    private $orderStatus;

    /**
     * @var \Amasty\Segments\Helper\Order\Data
     */
    protected $helper;

    /**
     * Common constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Sales\Model\Config\Source\Order\Status $orderStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus,
        \Amasty\Segments\Helper\Order\Data $orderHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderStatus = $orderStatus;
        $this->helper      = $orderHelper;
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'status'          => __('Order Status'),
            'days_was_placed' => __('Was placed (days) ago'),
            'created_at'      => __('Placed'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'days_was_placed':
                return 'day';
            case 'status':
                return 'select';
            case 'created_at':
                return 'date';
        }

        return 'string';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'status':
                return 'select';
        }

        return 'text';
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {

            switch ($this->getAttribute()) {
                case 'status':
                    $options = $this->orderStatus->toOptionArray();
                    break;

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }

        return $this->getData('value_select_options');
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
     * Return real Order attribute for validate
     *
     * @return string
     */
    protected function getEavAttributeCode()
    {
        switch ($this->getAttribute()) {
            case 'days_was_placed':
                return 'created_at';
        }

        return $this->getAttribute();
    }

    /**
     * @param \Magento\Sales\Model\Order $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        switch ($this->getAttribute()) {
            case 'days_was_placed':
                $attributeValue = $this->prepareDayValidation($model);

                return parent::validateAttribute($attributeValue);
        }

        return parent::validate($model);
    }
}
