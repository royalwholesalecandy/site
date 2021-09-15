<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\ResourceModel\Method;

/**
 * Class Commented
 */
class Commented extends Toprated
{
    /**
     * {@inheritdoc}
     */
    public function getMethodLabel($store = null)
    {
        $storeLabel = $this->helper->getScopeValue('reviews_count/label', $store);
        if ($storeLabel) {
            return $storeLabel;
        }

        return AbstractMethod::getMethodLabel($store);
    }

    /**
     * Returns Sorting method Table Column name
     * which is using for order collection
     *
     * @return string
     */
    public function getSortingColumnName()
    {
        $columnName = $this->helper->isYotpoEnabled() ? 'total_reviews' : 'reviews_count';

        return $columnName;
    }
}
