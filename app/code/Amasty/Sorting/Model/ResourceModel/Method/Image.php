<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\ResourceModel\Method;

use Amasty\Sorting\Model\Source\Image as ImageSource;
use Magento\Sitemap\Model\ResourceModel\Catalog\Product as ProductResource;

/**
 * Class Image
 * Method Using like additional sorting and not visible in the list of methods
 */
class Image extends AbstractMethod
{
    /**
     * {@inheritdoc}
     */
    public function getSortingColumnName()
    {
        return 'small_image';
    }

    /**
     * {@inheritdoc}
     */
    public function apply($collection, $direction = '')
    {
        if (!$this->isMethodActive($collection)) {
            return $this;
        }

        $collection->setFlag('amasty_image_sorted', true);
        $attribute = $this->getSortingColumnName();

        // process join for image is needed
        $collection->addAttributeToSort($attribute, $collection::SORT_ORDER_ASC);

        $orders = $collection->getSelect()->getPart(\Zend_Db_Select::ORDER);
        // move from the last to the the first position
        $last = array_pop($orders);
        // replace column by Expression
        $last[0] = $this->getSortExpression($last[0]);
        array_unshift($orders, $last);
        $collection->getSelect()->setPart(\Zend_Db_Select::ORDER, $orders);

        return $this;
    }

    /**
     * Is can apply method sorting
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     *
     * @return bool
     */
    private function isMethodActive($collection)
    {
        if ($collection->getFlag('amasty_image_sorted')) {
            return false;
        }

        $show = $this->helper->getScopeValue('general/no_image_last');

        if (!$show || ($show == ImageSource::SHOW_LAST_FOR_CATALOG && $this->isSearchModule())) {
            return false;
        }

        return true;
    }

    /**
     * Skip search results
     *
     * @return bool
     */
    private function isSearchModule()
    {
        return in_array(
            $this->request->getModuleName(),
            ['sqli_singlesearchresult', 'catalogsearch']
        );
    }

    /**
     * If image value is no_selection then drop value to down of the list
     * return IF(IFNULL(e.small_image, 'no_selection')='no_selection', 1, 0)
     *
     * @return \Zend_Db_Expr
     */
    private function getSortExpression($imageColumn)
    {
        $connection = $this->getConnection();
        $noSelection = $connection->quote(ProductResource::NOT_SELECTED_IMAGE);
        /** IFNULL(e.small_image, 'no_selection') */
        $ifNull = $connection->getIfNullSql($imageColumn, $noSelection);
        /** IFNULL(e.small_image, 'no_selection')='no_selection' */
        $ifNull .= '=' . $noSelection;

        /** IF(IFNULL(e.small_image, 'no_selection')='no_selection', 1, 0) */
        return $connection->getCheckSql($ifNull, 1, 0);
    }
}
