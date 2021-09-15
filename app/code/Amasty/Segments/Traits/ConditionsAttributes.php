<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Traits;

use Amasty\Segments\Helper\Condition\Data as ConditionHelper;

trait ConditionsAttributes
{
    /**
     * @var string
     */
    protected $propertyPrefix = 'condition';

    /**
     * @var array
     */
    protected $restrictProductAttributes = ['quote_item_price', 'quote_item_qty', 'quote_item_row_total'];

    /**
     * @var array
     */
    protected $attributesRuleMap = [
        'order'    => ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Order',
        'product'  => ConditionHelper::MAGENTO_SALES_RULE_PATH_TO_CONDITIONS . 'Product',
        'common'   => ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Order\Common',
        'shipping' => ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Address\Shipping',
        'billing'  => ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Address\Billing',
        'cart'     => ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Cart',
        'customer' => ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Customer',
    ];

    /**
     * @param string $method
     * @return array
     */
    public function getConditionAttributes($method = 'order')
    {
        $propertyName = $this->getPropertyName($method);
        $attributes = $this->{$propertyName}->loadAttributeOptions()->getAttributeOption();
        $result = [];

        foreach ($attributes as $code => $label) {

            if ($method == 'product' && in_array($code, $this->restrictProductAttributes)) {
                continue;
            }

            $result[] = [
                'value' => $this->getPathMap($method) . '|' . $code,
                'label' => $label,
            ];
        }

        return $result;
    }

    /**
     * @param $method
     * @return string
     */
    protected function getPropertyName($method)
    {
        return $this->propertyPrefix . ucfirst($method);
    }

    /**
     * @param $method
     * @return bool
     */
    protected function getPathMap($method)
    {
        return (array_key_exists($method, $this->attributesRuleMap) && $this->attributesRuleMap[$method])
            ? $this->attributesRuleMap[$method] : false;
    }
}
