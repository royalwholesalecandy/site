<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Model\QBXML;

use Magento\Sales\Model\Order as OrderModel;
use Magenest\QuickBooksDesktop\Model\QBXML;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\Model\Config\Source\Version;

/**
 * Class SalesOrder
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class SalesOrder extends QBXML
{
    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * @var OrderModel
     */
    protected $_order;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Mapping
     */
    public $_map;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * SalesOrder constructor.
     * @param OrderModel $order
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Mapping $map
     * @param ProductFactory $productFactory
     * @param \Magento\Framework\App\ObjectManager $objectManager
     * @param QueueHelper $queueHelper
     */
    public function __construct(
        OrderModel $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Mapping $map,
        ProductFactory $productFactory,
        ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        QueueHelper $queueHelper
    )
    {
        parent::__construct($objectManager);
        $this->_order = $order;
        $this->scopeConfig = $scopeConfig;
        $this->_map = $map;
        $this->_productFactory = $productFactory;
        $this->_queueHelper = $queueHelper;
        $this->_version = $this->_queueHelper->getQuickBooksVersion();
        $this->customerFactory = $customerFactory;
    }

    /**
     * Get XML using sync to QBD
     *
     * @param $id
     * @return string
     */
    public function getXml($id)
    {
        /** @var \Magento\Sales\Model\Order $model */
        $model = $this->_order->load($id);
        $billAddress = $model->getBillingAddress();
        $shipAddress = $model->getShippingAddress();
        $customerId = $model->getCustomerId();
        $orderStatus = $model->getStatus();
//        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/saleR.log');
//        $logger = new \Zend\Log\Logger();
//        $logger->addWriter($writer);
//        $logger->info("===".$id);
        if ($customerId) {
            $customer = $this->customerFactory->create()->load($customerId);
            if ($customer->getEntityId()) {
				$customerReceive = substr($this->removeSpecialChar($customer->getName()).' '. $customerId,0,40);
                //$customerReceive = $customer->getName() . ' ' . $customerId;
            } else {
                $customerReceive = substr($this->removeSpecialChar($model->getCustomerName()).' '. $customerId,0,40);
            }
        } else {
            
            $customerReceive = $billAddress->getName() .' '. $model->getIncrementId();
        }
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $xml = $this->multipleXml($customerReceive, ['CustomerRef', 'FullName']);
        //if($orderStatus == 'canceled'){
            //$xml .= $this->simpleXml(true, 'IsManuallyClosed');
            //$xml .=  '<IsManuallyClosed >'.true.'</IsManuallyClosed>';
        //}
        
		//$dateObj = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
		//$date = $dateObj->date()->format('Y-m-d\TH:i:sP');

        $xml .= $this->simpleXml(date('Y-m-d',strtotime($model->getCreatedAt())), 'TxnDate');
        
		$xml .= $this->simpleXml($model->getIncrementId(), 'RefNumber');
        $xml .= $billAddress ? $this->getAddress($billAddress) : '';
        $xml .= $shipAddress ? $this->getAddress($shipAddress, 'ship') : '';
		$character = 14;
		if(strtok($model->getShippingMethod(), '_') == 'customshipprice' || $model->getShippingMethod() == 'smashingmagazine' || $model->getShippingMethod() == 'wk_amzconnectship'){
			$character = 15;
		}
        $shipMethod = substr(trim(strtok($model->getShippingMethod(), '_')),0,$character);
		if($shipMethod == 'envato'){
			$shipMethod = 'customshipping';
		}
        if (!empty($model->getShippingMethod())) {
            $xml .= $this->multipleXml($shipMethod, ['ShipMethodRef', 'FullName']);
        }

        $taxCode = $this->getTaxCodeTransaction($model);
        if ($taxCode != null && $this->_version == Version::VERSION_US) {
            $xml .= $this->multipleXml($taxCode, ['ItemSalesTaxRef', 'FullName']);
        }

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($model->getAllItems() as $item) {
            $xml .= $this->getOrderItem($item);
        }
        $feeAmount = $model->getFeeAmount();
                
        if($feeAmount > 0){
            $dataCreditFee =
            [
            'type' => 'Credit Card',
            'desc' => 'Credit Card',
            'rate' => $feeAmount,
            'tax_amount' => 0,
            'txn_id' => null,
            'taxcode' => $taxCode
            ];
            $xml .= $this->getOtherItem($dataCreditFee, 'SalesOrderLineAdd');
            
        }
        if ($model->getShippingAmount() != 0) {
            $dataShipping =
                [
                    'type' => 'Shipping',
                    'desc' => $model->getShippingDescription(),
                    'rate' => $model->getShippingAmount(),
                    'tax_amount' => $model->getShippingTaxAmount(),
                    'txn_id' => null,
                    'taxcode' => $taxCode
                ];

            $xml .= $this->getOtherItem($dataShipping, 'SalesOrderLineAdd');
        }

        if ($model->getDiscountAmount() != 0) {
            $discount = $model->getDiscountAmount();
            if ($discount < 0) {
                $discount = 0 - $discount;
            }
            $dataDiscount =
                [
                    'type' => 'Discount',
                    'desc' => $model->getDiscountDescription(),
                    'rate' => $discount,
                    'tax_amount' => $model->getDiscountTaxCompensationAmount(),
                    'txn_id' => null,
                    'taxcode' => $taxCode
                ];
            $xml .= $this->getOtherItem($dataDiscount, 'SalesOrderLineAdd');
        }
//         $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/saleR.log');
//         $logger = new \Zend\Log\Logger();
//         $logger->addWriter($writer);
//         $logger->info($xml);
        return $xml;
    }

    /**
     * Get Order Item XML
     *
     * @param \Magento\Sales\Model\Order\Item $item *
     * @return string
     */
    protected function getOrderItem($item)
    {
        $price = $item->getPrice();
        $taxAmount = $item->getTaxAmount();

        $qty = $item->getQtyOrdered();
        $sku = $item->getSku();

        if ($sku) {
            if ($qty > 0 && $price > 0) {
                $hasTax = false;
                $taxCode = $this->getTaxCodeItem($item->getItemId());
                $xml = '<SalesOrderLineAdd>';
                $xml .= $this->multipleXml(substr(str_replace(['&', '”', '\'', '<', '>', '"'], ' ', $sku), 0, 30), ['ItemRef', 'FullName']);
                $xml .= $this->simpleXml($qty, 'Quantity');
                $xml .= $this->simpleXml($price, 'Rate');

                if ($taxAmount != 0) {
                    $hasTax = true;
                }
                $xml .= $this->getXmlTax($taxCode, $hasTax);
                $xml .= '</SalesOrderLineAdd>';
            }else{
                $hasTax = false;
                $taxCode = $this->getTaxCodeItem($item->getItemId());
                $xml = '<SalesOrderLineAdd>';
                $xml .= $this->multipleXml(substr(str_replace(['&', '”', '\'', '<', '>', '"'], ' ', $sku), 0, 30), ['ItemRef', 'FullName']);
                $xml .= $this->simpleXml($qty, 'Quantity');
                $xml .= $this->simpleXml($price, 'Rate');

                $xml .= $this->getXmlTax($taxCode, $hasTax);
                $xml .= '</SalesOrderLineAdd>';
            }
        } else {
            $xml = '<SalesOrderLineAdd>';
            $xml .= $this->multipleXml('Not Found Product From M2', ['ItemRef', 'FullName']);
            $xml .= '</SalesOrderLineAdd>';
            return $xml;
        }

        return $xml;
    }
}
