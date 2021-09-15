<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Model;

use \Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\Model\Config\Source\Version;

/**
 * Class QBXML
 * @package Magenest\QuickBooksDesktop\Model
 */
abstract class QBXML
{

    protected $_version;

    protected $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $id
     * @return string
     */
    public function getXml($id)
    {
        // TODO
        return '';
    }

    /** @var \Magento\Sales\Model\Order $order */
    public function getTaxCodeTransaction($order)
    {
        $taxCode = null;
        $taxOrder = $this->objectManager->create(\Magento\Sales\Model\Order\TaxFactory::class);
        $taxOrder = $taxOrder->create()->getCollection()->addFieldToFilter("order_id", $order->getId())->getFirstItem();
        $taxOrderCode = $taxOrder->getCode();
        $modelTaxCode = $this->objectManager->create(\Magenest\QuickBooksDesktop\Model\TaxCodeFactory::class)->create()
            ->getCollection()
            ->addFieldToFilter("tax_title", $taxOrderCode)
            ->getFirstItem();
        $modelTax = $this->objectManager->create(\Magenest\QuickBooksDesktop\Model\TaxFactory::class)->create()->load($modelTaxCode->getCode());
        if ($modelTax && !empty($modelTax->getData())) {
            $taxCode = $modelTax->getTaxCode();
        } elseif ($order->getTaxAmount() != 0) {
            /** @var \Magento\Tax\Model\Calculation\Rate $taxAlls */
            $taxAlls = $this->objectManager->create(\Magento\Tax\Model\Calculation\Rate::class)
                ->getCollection()->getItems();
            foreach ($taxAlls as $taxAll) {
                if ($taxAll->getRate() * $order->getBaseSubtotal() / 100 == $order->getTaxAmount()
                    || round($taxAll->getRate() * $order->getBaseSubtotal() / 100, 2) == $order->getTaxAmount()) {
                    $modelTaxCode = $this->objectManager->create(\Magenest\QuickBooksDesktop\Model\TaxCodeFactory::class)
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter("tax_title", $taxAll->getCode())
                        ->getFirstItem();
                    $modelTax = $this->objectManager->create(\Magenest\QuickBooksDesktop\Model\TaxFactory::class)
                        ->create()->load($modelTaxCode->getCode());
                    if ($modelTax && !empty($modelTax->getData())) {
                        $taxCode = $modelTax->getTaxCode();
                        break;
                    }
                }
            }
        }
        return $taxCode;
    }

    /**
     * Create Tax
     */
    public function getTaxCodeItem($itemId)
    {
        $modelTaxItem = $this->objectManager->create(\Magento\Sales\Model\Order\Tax\Item::class)->load($itemId, 'item_id');
        $taxCode = null;
        if ($modelTaxItem) {
            $taxId = $modelTaxItem->getTaxId();
            /** @var \Magento\Sales\Model\Order\Tax $modelTax */
            $modelTaxCode = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magenest\QuickBooksDesktop\Model\TaxCodeFactory::class)
                ->create()->getCollection()
                ->addFieldToFilter("tax_id", $taxId)
                ->getFirstItem();
            $modelTax = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magenest\QuickBooksDesktop\Model\TaxFactory::class)
                ->create()->load($modelTaxCode->getCode());
            if ($modelTax && !empty($modelTax->getData())) {
                $taxCode = $modelTax->getTaxCode();
            }
        }
        return $taxCode;
    }

    public function getXmlTax($code, $hasTax)
    {
        $version = $this->_version;
        if ($hasTax) {
            if ($version == Version::VERSION_US) {
                $xml = $this->multipleXml('Tax', ['SalesTaxCodeRef', 'FullName']);
            } elseif ($code) {
                $xml = $this->multipleXml($code, ['SalesTaxCodeRef', 'FullName']);
            }
        } else {
            if ($version == Version::VERSION_US) {
                $xml = $this->multipleXml('Non', ['SalesTaxCodeRef', 'FullName']);
            } elseif ($code) {
                $xml = $this->multipleXml('E', ['SalesTaxCodeRef', 'FullName']);
            }
        }
        return $xml;
    }

    public function simpleXml($value, $tag)
    {
        if ($value !== '') {
            return "<$tag>$value</$tag>";
        } else {
            return '';
        }
    }

    public function multipleXml($value, array $tags)
    {
        $xml = '';
        if ($value !== '') {
            foreach ($tags as $tag) {
                $xml .= "<$tag>";
            }
            $xml .= "$value";
            $tags = array_reverse($tags);
            foreach ($tags as $tag) {
                $xml .= "</$tag>";
            }
        }
        return $xml;
    }


    /**
     * @param $address
     * @param string $type
     * @return string
     */
    protected function getAddress($address, $type = 'bill')
    {
        if (!$address) {
            return '';
        }

        $countryId = ($address->getCountryId() ? $address->getCountryId() : 'US');
        $country = $this->objectManager->create(\Magento\Directory\Model\Country::class)->loadByCode($countryId);
		$fulladdress = $address->getStreetLine(1).' '.$address->getStreetLine(2);
		
		$fulladdress = $this->removeSpecialChar($fulladdress);
		if(strlen($fulladdress) > 41){
			$addressFinalLength = strlen($fulladdress)/2;
			$add1 = substr($fulladdress,0,$addressFinalLength);
			$add2 = substr($fulladdress,$addressFinalLength,strlen($fulladdress));
		}
		else{
			$add1 = $this->removeSpecialChar($address->getStreetLine(1));
			$add2 = $this->removeSpecialChar($address->getStreetLine(2));
		}
		
		$city = $this->removeSpecialChar($address->getCity());
		$state = $this->removeSpecialChar($address->getRegion());
		if(strtolower($add1) == 'na'){$add1 = ' ';}
		if(strtolower($add2) == 'na'){$add2 = ' ';}
		if(strtolower($city) == 'na'){$city = 'N/A';}
		if(strtolower($state) == 'na'){$state = 'N/A';}
		
        $xml = $type == 'bill' ? '<BillAddress>' : '<ShipAddress>';
        $xml .= $this->simpleXml(substr($this->removeSpecialChar($address->getName()),0,40), 'Addr1');
        $xml .= $this->simpleXml(substr($add1,0,40), 'Addr2');
        $xml .= $this->simpleXml(substr($add2,0,40), 'Addr3');
        $xml .= $this->simpleXml(substr($city,0,40), 'City');
        $xml .= $this->simpleXml(substr($state,0,20), 'State');
        $xml .= $this->simpleXml(substr($this->removeSpecialChar(trim($address->getPostcode())),0,12), 'PostalCode');
        $xml .= $this->simpleXml($country->getName(), 'Country');
        $xml .= $type == 'bill' ? '</BillAddress>' : '</ShipAddress>';

        return $xml;
    }

    /**
     * Get Other Item XML
     *
     * @param \Magento\Sales\Model\Order\Invoice\Item $item *
     * @return string
     */
    protected function getOtherItem($data, $tag)
    {
        $xml = "<$tag>";

        $txnLineId = null;
        if ($data['txn_id'] !== null) {
            $txnLineId = $this->getTnxLineId($data['txn_id'], $data['type']);
        }
        if (!$txnLineId) {
            $xml .= $this->multipleXml($data['type'], ['ItemRef', 'FullName']);
        }
        $xml .= $this->simpleXml($data['desc'], 'Desc');
        $xml .= $this->simpleXml($data['rate'], 'Rate');

        if ($data['tax_amount'] > 0) {
            $xml .= $this->getXmlTax($data['taxcode'], true);
        } else {
            $xml .= $this->getXmlTax($data['taxcode'], false);
        }

        if ($txnLineId) {
            $xml .= "<LinkToTxn>";
            $xml .= $this->simpleXml($data['txn_id'], 'TxnID');
            $xml .= $this->simpleXml($txnLineId, 'TxnLineID');
            $xml .= "</LinkToTxn>";
        }

        $xml .= "</$tag>";
        return $xml;
    }

    protected function getTnxLineId($txnId, $sku)
    {
        $companyId = $this->objectManager->create(\Magenest\QuickBooksDesktop\Helper\CreateQueue::class)->getCompanyId();
        $result = $this->objectManager->create(\Magenest\QuickBooksDesktop\Model\ItemSalesOrderFactory::class)->create()->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('list_id_order', $txnId)
            ->addFieldToFilter('sku', $sku)
            ->getLastItem()->getData();
        return @$result['txn_line_id'];
    }
	public function removeSpecialChar($text){
		return preg_replace('/[^A-Za-z0-9\. -]/', '',$text);
	}
}
