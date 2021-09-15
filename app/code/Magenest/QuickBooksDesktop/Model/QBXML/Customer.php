<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model\QBXML;

use Magento\Customer\Model\Customer as CustomerModel;
use Magenest\QuickBooksDesktop\Model\QBXML;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Class Customer
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class Customer extends QBXML
{
    /**
     * @var CustomerModel
     */
    protected $_customer;

    /**
     * Customer constructor.
     * @param CustomerModel $customer
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        CustomerModel $customer,
        ObjectManagerInterface $objectManager
    ) {
        $this->_customer = $customer;
        parent::__construct($objectManager);
    }

    /**
     * Get XML using sync to QBD
     *
     * @param  int $id
     * @return string
     */
    public function getXml($id)
    {
        /** @var \Magento\Customer\Model\Customer $model */
//        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/customer.log');
//            $logger = new \Zend\Log\Logger();
//            $logger->addWriter($writer);
//            $logger->info('coming');
        $model = $this->_customer->load($id);
        $billAddress = $model->getDefaultBillingAddress();
        $shipAddress = $model->getDefaultShippingAddress();
        $xml = $this->simpleXml(substr($this->removeSpecialChar($model->getName()).' '. $id,0,40), 'Name');
        $xml .= $billAddress ? $this->simpleXml(substr($this->removeSpecialChar($billAddress->getCompany()),0,40), 'CompanyName') : '';

        $xml .= $this->simpleXml(substr($this->removeSpecialChar($model->getFirstname()),0,40), 'FirstName');
		$phone = $billAddress ? substr($this->removeSpecialChar($billAddress->getTelephone()),0,15):'';
        $xml .= $this->simpleXml(substr($this->removeSpecialChar($model->getLastname()),0,40), 'LastName');
        $xml .= $this->getAddress($billAddress);
        $xml .= $this->getAddress($shipAddress, 'ship');
		$xml .= $this->getListAddress($model);
        $xml .= $billAddress ? $this->simpleXml($phone, 'Phone') : '';
        $xml .= $this->simpleXml($model->getEmail(), 'Email');
        $xml .= $this->simpleXml($model->getId(), 'AccountNumber');
        //$xml .= $this->simpleXml($model->getCustomPoLimit(), 'CreditLimit');
        //$xml .= $this->simpleXml($model->getCustomNetTerms(), 'JobTitle');
        
        //$xml .= '<TermsRef>';
        //$xml .= $this->simpleXml($model->getCustomNetTerms(), 'FullName');
        //$xml .= $this->simpleXml('8000CD06-1562249154', 'ListID');
//        $xml .= $this->simpleXml('COD', 'FullName');
//        $xml .= '</TermsRef>';
//        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/customer.log');
//        $logger = new \Zend\Log\Logger();
//        $logger->addWriter($writer);
//        $logger->info($xml);
        return $xml;
    }
	public function getListAddress($model){
		$xml = '';
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
		$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($model->getId());
		foreach ($customer->getAddresses() as $address)
		{
            
            $countryId = ($address->getCountryId() ? $address->getCountryId() : 'US');
			$country = $objectManager->create(\Magento\Directory\Model\Country::class)->loadByCode($countryId);
			$fulladdress = $address->getStreetLine(1).' '.$address->getStreetLine(2);
			$fulladdress = $this->removeSpecialChar($fulladdress);
			$addressFinalLength = strlen($fulladdress)/2;
			$add1 = substr($fulladdress,0,$addressFinalLength);
			$add2 = substr($fulladdress,$addressFinalLength,strlen($fulladdress));
			$xml .= '<ShipToAddress>';
			$xml .= $this->simpleXml(substr($this->removeSpecialChar($address->getName()),0,40), 'Name');
			$xml .= $this->simpleXml(substr($this->removeSpecialChar($address->getName()),0,40), 'Addr1');
			$xml .= $this->simpleXml($add1, 'Addr2');
			$xml .= $this->simpleXml($add2, 'Addr3');
			$xml .= $this->simpleXml(substr($this->removeSpecialChar($address->getCity()),0,40), 'City');
			$xml .= $this->simpleXml(substr($this->removeSpecialChar($address->getRegion()),0,20), 'State');
			$xml .= $this->simpleXml(substr($this->removeSpecialChar(trim($address->getPostcode())),0,12), 'PostalCode');
			$xml .= $this->simpleXml($country->getName(), 'Country');
			$xml .= '</ShipToAddress>';
			$customerAddress[] = $address->getName();
		}
		return $xml;
	}
}
