<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Akeans\Pocustomize\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function updatePoCredit($order, $invoice){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$customerSession = $objectManager->create('Magento\Customer\Model\Session');
		$customerId = $order->getCustomerId();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$websiteId = $storeManager->getWebsite()->getWebsiteId();
		$customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
		$customer = $customerFactory->load($customerId);
		$grandTotal = $invoice->getGrandTotal();
		$poCredit = (float)$customer->getCustomPoCredit();
		if($poCredit >= $grandTotal){
			$totalDueAmount = $poCredit - $grandTotal;
			$customerObj = $objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface')->getById($order->getCustomerId());
			$customerObj->setWebsiteId($websiteId);
			$customerObj->setCustomAttribute('custom_po_credit', $totalDueAmount);
			$objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface')->save($customerObj);
		}
		
		//$customer->setCustomPoCredit($totalDueAmount)->save();
	}
}
