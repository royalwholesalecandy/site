<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition;

/**.
 * @method string getAttribute() current condition attribute available code in loadAttributeOptions
 */
class Condition extends \Magento\Rule\Model\Condition\AbstractCondition
{
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
     * Retrieve operator for php validation
     *
     * @return string
     */
    public function getOperatorForValidate()
    {
        if ($this->getInputType() == 'day') {
            // if comparison by days, then need to swap $value and $validatedValue for correct result
            switch ($this->getOperator()) {
                case '>=':
                    return '<=';
                case '<=':
                    return '>=';
                case '>':
                    return '<';
                case '<':
                    return '>';
            }
        }

        return $this->getOperator();
    }

    /**
     * @param string $operator
     * @return string
     */
    public function reverseOperatorOptions($operator)
    {
        switch ($operator) {
            case '>=':
                return '<=';
            case '<=':
                return '>=';
            case '>':
                return '<';
            case '<':
                return '>';
            default:
                return $operator;
        }
    }
}
