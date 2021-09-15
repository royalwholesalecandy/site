<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition\Product;

use Amasty\Segments\Helper\Condition\Data as ConditionHelper;

class Subselect extends \Amasty\Segments\Model\Rule\Condition\Product\Combine
{

    /**
     * use MainValidation trait
     */
    use \Amasty\Segments\Traits\MainValidation;

    /**
     * @var string
     */
    protected $type;

    /**
     * Subselect constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $conditionProduct, $data);
        $this->setType(ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Product\Subselect')->setValue(null);
    }

    /**
     * Load array
     *
     * @param array $arr
     * @param string $key
     * @return $this
     */
    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);

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
                "If %1 %2 %3 for a subselection of items in $this->type product matching %4 of these conditions:",
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
     * Return as xml
     *
     * @param string $containerKey
     * @param string $itemKey
     * @return string
     */
    public function asXml($containerKey = 'conditions', $itemKey = 'condition')
    {
        $xml = '<attribute>' .
            $this->getAttribute() .
            '</attribute>' .
            '<operator>' .
            $this->getOperator() .
            '</operator>' .
            parent::asXml(
                $containerKey,
                $itemKey
            );

        return $xml;
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
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        return $this;
    }

    /**
     * @return \Magento\Reports\Model\ResourceModel\Product\Index\Viewed\Collection
     * |\Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    public function getValidationCollection($customer)
    {
        return $this;
    }

    /**
     * Load operator options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                '==' => __('is'),
                '!=' => __('is not'),
                '>=' => __('equals or greater than'),
                '<=' => __('equals or less than'),
                '>' => __('greater than'),
                '<' => __('less than'),
                '()' => __('is one of'),
                '!()' => __('is not one of'),
            ]
        );
        
        return $this;
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $model = $this->objectValidation($model);

        if (!$this->getConditions() || !$model || $model->getCustomerIsGuest()) {
            return false;
        }

        $total = 0;

        foreach ($this->getValidationCollection($model) as $item) {
            if (parent::validate($item)) {
                $total++;
            }
        }

        return $this->validateAttribute($total);
    }
}
