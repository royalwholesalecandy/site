<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\ResourceModel\Method;

/**
 * Class Newest
 */
class Newest extends AbstractMethod
{
    /**
     * {@inheritdoc}
     */
    public function getMethodLabel($store = null)
    {
        $storeLabel = $this->helper->getScopeValue('new/label', $store);
        if ($storeLabel) {
            return $storeLabel;
        }

        return parent::getMethodLabel($store);
    }

    public function getSortingColumnName()
    {
        $attributeCode = $this->helper->getScopeValue('new/new_attr');
        if ($attributeCode) {
            return $attributeCode;
        }

        return 'created_at';
    }

    /**
     * {@inheritdoc}
     */
    public function apply($collection, $direction)
    {
        $attributeCode = $this->getSortingColumnName();

        $collection->addAttributeToSort($attributeCode, $direction);

        return $this;
    }
}
