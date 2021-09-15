<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Traits;

/**
 * Used in Conditions.
 * Add Input type "day"
 */
trait DayValidation
{
    /**
     * Return real Order attribute for validate
     *
     * @return string
     */
    protected function getEavAttributeCode()
    {
        return $this->getAttribute();
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        $operators = parent::getDefaultOperatorInputByType();
        $operators['day'] = ['==', '>=', '>', '<=', '<'];

        return $operators;
    }

    /**
     * Prepare date diff
     * Reformat model date from Y-m-d H:i:s to Y-m-d for correct day comparsion
     * return value ready for validateAttribute
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     *
     * @return string
     */
    protected function prepareDayValidation(\Magento\Framework\Model\AbstractModel $model)
    {
        $this->setValueParsed($this->helper->getDateDiffFormat($this->getValue(), 'Y-m-d'));

        if (!$model->hasData($this->getEavAttributeCode())) {
            $model->load($model->getId());
        }
        $attributeValue = $model->getData($this->getEavAttributeCode());
        if (!is_string($attributeValue)) {
            return $attributeValue;
        }

        // change date format
        return $this->helper->getFormatDate($attributeValue);
    }
}
