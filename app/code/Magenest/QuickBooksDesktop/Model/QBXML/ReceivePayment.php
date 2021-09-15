<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
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
use Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;

/**
 * Class Customer
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class ReceivePayment extends QBXML
{
    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

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
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * Invoice constructor.
     * @param InvoiceModel $invoice
     * @param Connector $connector
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
     * @param $id
     * @return string
     */
    public function getXml($id)
    {
        $model = $this->_invoice->load($id);
        $order = $model->getOrder();
        $customerName = $order->getCustomerName();
        $customerId = $order->getCustomerId();
        if ($customerId) {
            $customer = $this->customerFactory->create()->load($customerId);
            if ($customer->getEntityId()) {
				// $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test1.log');
				// $logger = new \Zend\Log\Logger();
				// $logger->addWriter($writer);
				// $logger->info('test');
				$customerReceive = substr($this->removeSpecialChar($customer->getName()).' '. $customerId,0,40);
                //$customerReceive = $customer->getName() . ' ' . $customerId;
            } else {
				// $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test2.log');
				// $logger = new \Zend\Log\Logger();
				// $logger->addWriter($writer);
				// $logger->info('test');
                $customerReceive = $customerName . ' ' . $customerId;
            }
        } else {
			// $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test3.log');
			// 	$logger = new \Zend\Log\Logger();
			// 	$logger->addWriter($writer);
			// 	$logger->info('test');
            $customerReceive = $this->removeSpecialChar($order->getCustomerFirstname().' '.$order->getCustomerLastName(). ' ' . $order->getIncrementId(),0,40) ;
        }
        $companyId = $this->_queueHelper->getCompanyId();
        $xml = $this->multipleXml($customerReceive, ['CustomerRef', 'FullName']);
        $xml .= $this->simpleXml(date('Y-m-d',strtotime($model->getCreatedAt())), 'TxnDate');
        $xml .= $this->simpleXml($model->getIncrementId(), 'RefNumber');

        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        $xml .= $this->simpleXml(str_replace(',', '', number_format($model->getBaseGrandTotal(), 2)), 'TotalAmount');

        if ($paymentMethod) {
            $xml .= $this->multipleXml($paymentMethod, ['PaymentMethodRef', 'FullName']);
        }

        $txnid = $this->_map->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('type', Type::QUEUE_INVOICE)
            ->addFieldToFilter('entity_id', $id)
            ->getLastItem()
            ->getData('list_id');
		//$txnid = '';
        if (!empty($txnid)) {
            $xml .= '<AppliedToTxnAdd>';
            $xml .= '<TxnID useMacro="MACROTYPE">' . $txnid . '</TxnID>';
            $xml .= $this->simpleXml(str_replace(',', '', number_format($model->getBaseGrandTotal(), 2)), 'PaymentAmount');
            $xml .= '</AppliedToTxnAdd>';
        } else {
            $xml .= '<AppliedToTxnAdd>';
            $xml .= '<TxnID useMacro="MACROTYPE">Not Found Txn Id Invoice</TxnID>';
            $xml .= '</AppliedToTxnAdd>';
        }
		/*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/payment.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info($xml);*/
        return $xml;
        return $xml;
    }
}
