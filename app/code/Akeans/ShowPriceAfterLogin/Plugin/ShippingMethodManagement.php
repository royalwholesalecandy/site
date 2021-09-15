<?php

/**
 * custom shipping
 * Copyright (C) 2017  exinent
 * 
 * This file included in Akeans\ShowPriceAfterLogin is licensed under OSL 3.0
 * 
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Akeans\ShowPriceAfterLogin\Plugin;
use \Magento\Quote\Model\Quote;


class ShippingMethodManagement{
	
	
	
	public function afterCollectCarrierRates(\Magento\Shipping\Model\Shipping $subject, $result)
	{
//        $writer = new \Zend\Log\Writer\Stream(BP.'/var/log/akeans9.log');
//        $logger = new \Zend\Log\Logger();
//        $logger->addWriter($writer);
//        $logger->info($subject->getAllRates());
	}

   

    
	

}
