<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Block\Category\View;

use Mageplaza\DailyDeal\Block\Product\View\Label as DealLabel;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class Label
 * @package Mageplaza\DailyDeal\Block\Category\View
 */
class Label extends DealLabel
{
    /**
     * Get ProductIds by Category
     *
     * @return array
     */
    public function getProductIdsByCategory()
    {
        $productIds = [];
        $catId      = $this->_helperData->getCurrentCategory()->getId();

        $category   = $this->_categoryFactory->create()->load($catId);
        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addCategoryFilter($category);
        foreach ($collection as $item) {
            $productIds[] = $item->getData('entity_id');
        }

        return $productIds;
    }

    /**
     * Data label
     *
     * @return string
     */
    public function getDataLabel()
    {
        $labelData = [];

        foreach ($this->getProductIdsByCategory() as $id) {
            if ($this->_helperData->checkDealProduct($id)) {
                $labelData[$id] = $this->getLabel($this->getPercentDiscount($id));
            } else {
                if ($this->_helperData->checkDealConfigurableProduct($id)) {
                    $labelData[$id] = $this->getLabel($this->getMaxPercent($id));
                }
            }
        }

        return HelperData::jsonEncode($labelData);
    }

    /**
     * Get Full action name
     *
     * @return mixed
     */
    public function getFullActionName()
    {
        return $this->getRequest()->getFullActionName();
    }
}