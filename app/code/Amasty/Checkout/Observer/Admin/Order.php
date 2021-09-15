<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class Order implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$order = $observer->getEvent()->getOrder();
		$histories = $order->getStatusHistories();
		if(count($histories) > 1){
			foreach($histories as $history){
				if($history->getComment()){
					$history->setIsVisibleOnFront(true)->save();
				}
			}
		}
    }
}
