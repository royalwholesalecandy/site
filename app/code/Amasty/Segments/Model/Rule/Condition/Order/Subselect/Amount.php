<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition\Order\Subselect;

class Amount extends \Amasty\Segments\Model\Rule\Condition\Order\Subselect
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
            . 'Order\Subselect\Amount')->setValue(null);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(['base_grand_total' => __('total amount')]);
        return $this;
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
        $attr = $this->getAttribute();
        $total = 0;
        $model = $this->objectValidation($model);
        $orders = $this->orderHelper->getCollectionByCustomerType($model);

        if ($orders) {
            foreach ($orders as $item) {
                if (parent::validate($item)) {
                    $total += $item->getData($attr);
                }
            }
            return $this->validateAttribute($total);
        }

        return false;
    }
}
