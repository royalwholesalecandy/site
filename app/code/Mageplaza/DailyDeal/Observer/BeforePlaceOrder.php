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
use Mageplaza\DailyDeal\Helper\Data;

/**
 * Class BeforePlaceOrder
 * @package Mageplaza\DailyDeal\Observer
 */
class BeforePlaceOrder implements ObserverInterface
{
    /**
     * @var \Mageplaza\DailyDeal\Helper\Data
     */
    protected $_helperData;

    /**
     * BeforePlaceOrder constructor.
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->_helperData = $helperData;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $items = $order->getAllVisibleItems();

        foreach ($items as $item) {
            $productId = $item->getProductId();
            if (($this->_helperData->checkEndedDeal($productId) || $this->_helperData->checkDisableDeal($productId))
                && ($item->getPrice() != $item->getOriginalPrice())) {
                throw new LocalizedException(__('The deal on %1 product has ended', $item->getName()));
            }
        }

        return $this;
    }
}