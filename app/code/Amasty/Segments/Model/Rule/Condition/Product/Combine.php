<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition\Product;

use Amasty\Segments\Traits\ConditionsAttributes;
use Amasty\Segments\Helper\Condition\Data as ConditionHelper;

class Combine extends \Magento\Rule\Model\Condition\Combine
{

    /**
     * use ConditionsAttributes trait
     */
    use ConditionsAttributes;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product
     */
    protected $conditionProduct;

    /**
     * Combine constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->conditionProduct = $conditionProduct;
        $this->setType(ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Product\Combine');
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                ['label' => __('Product Attribute'), 'value' => $this->getConditionAttributes('product')]
            ]
        );

        return $conditions;
    }
}
