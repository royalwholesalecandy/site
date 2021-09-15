<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition\Order;

use Amasty\Segments\Traits\ConditionsAttributes;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
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
     * @var Common
     */
    private $conditionCommon;

    /**
     * Combine constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct,
        \Amasty\Segments\Model\Rule\Condition\Order\Common $conditionCommon,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->conditionProduct = $conditionProduct;
        $this->setType(ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Order\Combine');
        $this->conditionCommon = $conditionCommon;
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
                ['label' => __('Product Attribute'), 'value' => $this->getConditionAttributes('product')],
                ['label' => __('Common'), 'value' => $this->getConditionAttributes('common')]
            ]
        );

        return $conditions;
    }

    /**
     * Collect validated attributes
     *
     * @param Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }

    /**
     * Is entity valid
     *
     * @param int|\Magento\Framework\Model\AbstractModel $entity
     * @return bool
     */
    protected function _isValid($entity)
    {
        if (!$this->getConditions()) {
            return true;
        }

        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();

        foreach ($this->getConditions() as $cond) {

            if ($cond instanceof \Magento\SalesRule\Model\Rule\Condition\Product) {
                if ($entity instanceof \Magento\Sales\Model\Order) {
                    $allProducts = $entity->getAllVisibleItems();
                    $validated = false;

                    foreach ($allProducts as $product) {
                        if ($cond->validate($product)) {
                            $validated = true;

                            break;
                        }
                    }
                } else {
                    $validated = $cond->validate($entity);
                }
            } else {
                if ($entity instanceof \Magento\Framework\Model\AbstractModel) {
                    $validated = $cond->validate($entity);
                } else {
                    $validated = $cond->validateByEntityId($entity);
                }
            }

            if ($all && $validated !== $true) {
                return false;
            } elseif (!$all && $validated === $true) {
                return true;
            }
        }

        return $all ? true : false;
    }
}
