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

namespace Mageplaza\DailyDeal\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class SetDiscountPriceDealInCart
 * @package Mageplaza\DailyDeal\Observer
 */
class SetPriceDealInCart implements ObserverInterface
{
    /**
     * @var \Mageplaza\DailyDeal\Helper\Data
     */
    protected $_helperData;

    /**
     * SetDiscountPriceDealInCart constructor.
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->_helperData = $helperData;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        $item = $observer->getEvent()->getData('quote_item');

        /** check configuration product **/
        if ($item->getHasChildren()) {
            foreach ($item->getChildren() as $child) {
                $productId = $child->getProduct()->getId();
            }
        } else {
            $productId = $item->getProduct()->getId();
        }

        if ($this->_helperData->checkDealProduct($productId)) {
            $dealData  = $this->_helperData->getProductDeal($productId);
            $remainQty = $dealData->getDealQty() - $dealData->getSaleQty();
            $qty       = $item->getQty();

            if ($qty <= $remainQty) {
                $price = $this->_helperData->getDealPrice($productId);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
            } else {
                throw new LocalizedException(__('There\'s only %1 product(s) left, please try again with a smaller quantity', $remainQty));
            }
        }

        return $this;
    }
}
