<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition\Order\Subselect;

class Ordered extends \Amasty\Segments\Model\Rule\Condition\Order\Subselect
{
    /**
     * Subselect constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct,
        \Amasty\Segments\Model\Rule\Condition\Order\Common $conditionCommon,
        \Amasty\Segments\Helper\Order\Data $orderHelper,
        array $data = []
    ) {
        parent::__construct($context, $conditionProduct, $conditionCommon, $orderHelper, $data);
        $this->setType(\Amasty\Segments\Helper\Condition\Data::AMASTY_SEGMENTS_PATH_TO_CONDITIONS
            . 'Order\Subselect\Ordered')->setValue(null);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(['qty' => __('total quantity')]);
        return $this;
    }

    /**
     * Return as html
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . __(
                "If %1 %2 %3 for a subselection of products for orders matching %4 of these conditions:",
                $this->getAttributeElement()->getHtml(),
                $this->getOperatorElement()->getHtml(),
                $this->getValueElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = \Magento\Rule\Model\Condition\AbstractCondition::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                ['label' => __('Product Attribute'), 'value' => $this->getConditionAttributes('product')],
            ]
        );

        return $conditions;
    }

    /**
     * Validate
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        if (!$this->getConditions()) {
            return false;
        }

        $total = 0;
        $model = $this->objectValidation($model);
        $orders = $this->orderHelper->getCollectionByCustomerType($model);

        if ($orders) {
            foreach ($orders as $order) {
                foreach ($order->getAllVisibleItems() as $item) {
                    if (parent::validate($item)) {
                        $total ++;
                    }
                }
            }
            return $this->validateAttribute($total);
        }

        return false;
    }
}
