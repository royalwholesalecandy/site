<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Model\QBXML;

use Magento\Sales\Model\Order\Invoice as InvoiceModel;
use Magenest\QuickBooksDesktop\Model\QBXML;
use Magenest\QuickBooksDesktop\Model\Connector;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Class Customer
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class Invoice extends QBXML
{
    /**
     * @var InvoiceModel
     */
    protected $_invoice;

    /**
     * @var Mapping
     */
    public $_map;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;


    /**
     * Invoice constructor.
     * @param InvoiceModel $invoice
     * @param Connector $connector
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Mapping $map
     * @param ObjectManagerInterface $objectManager
     * @param QueueHelper $queueHelper
     */
    public function __construct(
        InvoiceModel $invoice,
        Connector $connector,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Mapping $map,
        ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        QueueHelper $queueHelper
    )
    {
        parent::__construct($objectManager);
        $this->connector = $connector;
        $this->_invoice = $invoice;
        $this->scopeConfig = $scopeConfig;
        $this->_map = $map;
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
        $invoice = $this->_invoice->load($id);
        $model = $invoice->getOrder();
        $billAddress = $model->getBillingAddress();
        $shipAddress = $model->getShippingAddress();
        $customerName = $model->getCustomerName();
        $customerId = $model->getCustomerId();

        if ($customerId) {
            $customer = $this->customerFactory->create()->load($customerId);
            if ($customer->getEntityId()) {
				
				$customerReceive = substr($this->removeSpecialChar($customer->getName()).' '. $customerId,0,40);
                //$customerReceive = $customer->getName() . ' ' . $customerId;
            } else {
				
                $customerReceive = $customerName . ' ' . $customerId;
            }
        } else {
			
            $customerReceive = $this->removeSpecialChar($model->getCustomerFirstname().' '.$model->getCustomerLastName(). ' ' . $model->getIncrementId(),0,40) ;
        }

        $xml = $this->multipleXml($customerReceive, ['CustomerRef', 'FullName']);
        $xml .= $this->simpleXml(date('Y-m-d',strtotime($invoice->getCreatedAt())), 'TxnDate');
        $xml .= $this->simpleXml($invoice->getIncrementId(), 'RefNumber');
        $xml .= $billAddress ? $this->getAddress($billAddress) : '';
        $xml .= $shipAddress ? $this->getAddress($shipAddress, 'ship') : '';
        //$shipMethod = substr(strtok($model->getShippingMethod(), '_'),0,14);
		$character = 14;
		if(strtok($model->getShippingMethod(), '_') == 'customshipprice'){
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

        if ($taxCode != null && $this->_version == 2) {
            $xml .= $this->multipleXml($taxCode, ['ItemSalesTaxRef', 'FullName']);
        }

        $companyId = $this->_queueHelper->getCompanyId();

        $txn_id = $this->_map->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('type', '3')
            ->addFieldToFilter('entity_id', $model->getId())
            ->getLastItem()
            ->getData('list_id');
		//$txn_id = '';
//        $xml .= $this->simpleXml($txn_id, 'LinkToTxn');

        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        foreach ($invoice->getAllItems() as $item) {
            $xml .= $this->getOrderItem($item, $txn_id);
        }

        if ($invoice->getShippingAmount() != 0) {
            $dataShipping =
                [
                    'type' => 'Shipping',
                    'desc' => str_replace('&','',$model->getShippingDescription()),
                    'rate' => $invoice->getShippingAmount(),
                    'tax_amount' => $invoice->getShippingTaxAmount(),
                    'txn_id' => $txn_id,
                    'taxcode' => $taxCode
                ];

            $xml .= $this->getOtherItem($dataShipping, 'InvoiceLineAdd');
        }

        if ($invoice->getDiscountAmount() != 0) {
            $discount = $invoice->getDiscountAmount();
            if ($discount < 0) {
                $discount = 0 - $discount;
            }
            $dataDiscount =
                [
                    'type' => 'Discount',
                    'desc' => $model->getDiscountDescription(),
                    'rate' => $discount,
                    'tax_amount' => $invoice->getDiscountTaxCompensationAmount(),
                    'txn_id' => $txn_id,
                    'taxcode' => $taxCode
                ];

            $xml .= $this->getOtherItem($dataDiscount, 'InvoiceLineAdd');
        }
		/*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/invoice.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info($xml);*/
        return $xml;
    }

    /**
     * Get Order Item XML
     *
     * @param \Magento\Sales\Model\Order\Invoice\Item $item *
     * @return string
     */
    protected function getOrderItem($item, $txn_id)
    {
        $price = $item->getPrice();
        $taxAmount = $item->getTaxAmount();

        $qty = $item->getQty();
        $sku = $item->getSku();

        if ($sku) {
            if ($qty > 0 && $price > 0) {
                $hasTax = false;
                $taxCode = $this->getTaxCodeItem($item->getParentId());
                $txnLineId = $this->getTnxLineId($txn_id, $sku);

                $xml = '<InvoiceLineAdd>';

                if (!$txnLineId) {
                    $xml .= $this->multipleXml(substr(str_replace(['&', '”', '\'', '<', '>', '"'], ' ', $sku), 0, 30), ['ItemRef', 'FullName']);
                }
                $xml .= $this->simpleXml($qty, 'Quantity');
                $xml .= $this->simpleXml($price, 'Rate');

                if ($taxAmount != 0) {
                    $hasTax = true;
                }
                $xml .= $this->getXmlTax($taxCode, $hasTax);

                if ($txnLineId) {
                    $xml .= "<LinkToTxn>";
                    $xml .= $this->simpleXml($txn_id, 'TxnID');
                    $xml .= $this->simpleXml($txnLineId, 'TxnLineID');
                    $xml .= "</LinkToTxn>";
                }
                $xml .= '</InvoiceLineAdd>';
            }
        } else {
            $xml = '<InvoiceLineAdd>';
            $xml .= $this->multipleXml('Not Found Product From M2', ['ItemRef', 'FullName']);
            $xml .= '</InvoiceLineAdd>';
            return $xml;
        }

        return $xml;
    }
}
