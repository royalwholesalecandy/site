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

namespace Akeans\ShowPriceAfterLogin\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\Quote;

class ShippingMethodManagement extends \Magento\Quote\Model\ShippingMethodManagement implements
\Magento\Quote\Api\ShippingMethodManagementInterface, \Magento\Quote\Model\ShippingMethodManagementInterface, ShipmentEstimationInterface {

    /**
     * @inheritdoc
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        return $this->getShippingMethods($quote, $address->getData());
    }
	/**
     * Get estimated rates
     *
     * @param Quote $quote
     * @param int $country
     * @param string $postcode
     * @param int $regionId
     * @param string $region
     * @param \Magento\Framework\Api\ExtensibleDataInterface|null $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     * @deprecated 100.2.0
     */
    protected function getEstimatedRates(
        \Magento\Quote\Model\Quote $quote,
        $country,
        $postcode,
        $regionId,
        $region,
        $address = null
    ) {
        if (!$address) {
            $address = $this->getAddressFactory()->create()
                ->setCountryId($country)
                ->setPostcode($postcode)
                ->setRegionId($regionId)
                ->setRegion($region);
        }
        return $this->getShippingMethods($quote, $address->getData());
    }
	/**
     * {@inheritDoc}
     */
    public function estimateByAddressId($cartId, $addressId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$adObj = $objectManager->create('\Magento\Customer\Model\AddressFactory');
        $address = $adObj->create()->load($addressId);

        return $this->getShippingMethods($quote, $address->getData());
    }
    /**
     * Get list of available shipping methods
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Framework\Api\ExtensibleDataInterface $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
	private function getShippingMethods(Quote $quote, array $addressData) {
        $output = [];
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $objectManager->create('Akeans\ShowPriceAfterLogin\Helper\Data');
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->addData($addressData);
        $shippingAddress->setCollectShippingRates(true);
		$codes=[];
		$codes1=[];
        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        $free=false;
		$freeOutput=[];
//        echo "=====";
//        die;
		foreach ($shippingRates as $carrierRates) {
			foreach ($carrierRates as $rate) {
				if($rate->getCode()=='freeshipping_freeshipping' || $rate->getCode()=='freeshipping')
				{
					$free=true;
					$freeOutput[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
					$codes[] = $rate->getCode();
				}else{
					$output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
					$codes1[] = $rate->getCode();
				}
			}
		}
//         $writer = new \Zend\Log\Writer\Stream(BP.'/var/log/akeans5.log');
//        $logger = new \Zend\Log\Logger();
//        $logger->addWriter($writer);
//        $logger->info($codes);
//        $writer = new \Zend\Log\Writer\Stream(BP.'/var/log/akeans6.log');
//        $logger = new \Zend\Log\Logger();
//        $logger->addWriter($writer);
//        $logger->info($codes1);
		if($free==true && $helper->isFreeShippingAvailable($quote))
		{
		return $freeOutput;
		}else{
		return $output;
		}
       
    }
	

}
