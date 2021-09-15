<?php
namespace Magewares\MWQuickOrder\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	 public function getUrl(){
	   $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeInterface = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$storeId = $storeInterface->getStore()->getId();
		$productUrl=$objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore($storeId)
            ->getBaseUrl();
		return $productUrl;
   }
   
   public function getSession(){
	   $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$session = $objectManager->get('\Magento\Customer\Model\Session');
		return $session;
   }
}