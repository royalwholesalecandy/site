<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Akeans\Pocustomize\Controller\Capture;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\Dir;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Rest\Request;
/**
 * Class Start
 * @package Magenest\QuickBooksDesktop\Controller\Connector
 */
class Index extends Action
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;
 
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;
 	protected $_invoiceSender;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;
 
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
		\Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
		$this->invoiceSender = $invoiceSender;
        parent::__construct($context);
    }
	public function canMakePayment($order)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            //$invoice = $this->_initInvoice();
			$invoice = $objectManager->create('\Magento\Sales\Model\Order\Invoice')->load($this->getRequest()->getParam('invoice_id'));
		if($invoice->getState() == 1){
			return true;
		}
        if ($order->getPayment() && ($order->getPayment()->getMethod() == 'purchaseorder') && $order->canShip()) {
            return false;
        }
        if ($order->getStatus() != 'purchaseorder_pending_payment') {
            return false;
        }
        if (!$order->canInvoice()) {
            return false;
        }
        return true;
    }
    public function execute()
    {
		//print_r($this->getRequest()->getParams());die;
        try {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            //$invoice = $this->_initInvoice();
			$invoice = $objectManager->create('\Magento\Sales\Model\Order\Invoice')->load($this->getRequest()->getParam('invoice_id'));
            if ($invoice) {
                $invoice->setRequestedCaptureCase('online');
                $paymentInfo = $this->getRequest()->getPost('payment', array());
				if (!$this->canMakePayment($invoice->getOrder())) {
                    throw new \Exception($this->__('Payment cannot be applied for this order.'));
                }
                
				
				//$this->_invoiceSender = $objectManager->get('\Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
				//echo is_array($paymentInfo).'---'.count($paymentInfo).'---'.isset($paymentInfo['method']);die;
                if (is_array($paymentInfo) && count($paymentInfo) && isset($paymentInfo['method'])) {
					//echo 'test';die;
                    /*if (!$this->_validatePaymentMethod($invoice->getOrder(), $paymentInfo['method'])) {
                        throw new \Exception(__('This payment method could not be used.'));
                    }*/
					$manager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
					$store = $manager->getStore($invoice->getOrder()->getStoreId());
                    $capturePayment = $objectManager->create('Akeans\Pocustomize\Model\Order\Popayment');
                    $capturePayment->setOrder($invoice->getOrder());
                    $capturePayment->importData($paymentInfo, $invoice->getOrder()->getStoreId());
                   // $capturePayment->setAmountOrdered($invoice->getOrder()->getTotalDue());
                   // $capturePayment->setBaseAmountOrdered($invoice->getOrder()->getBaseTotalDue());
                   // $capturePayment->setShippingAmount($invoice->getOrder()->getShippingAmount());
                  //  $capturePayment->setBaseShippingAmount($invoice->getOrder()->getBaseShippingAmount());
					//print_r($paymentInfo);echo '----';
                  //  $capturePayment->setAmountAuthorized($invoice->getOrder()->getTotalDue());
                  //  $capturePayment->setBaseAmountAuthorized($invoice->getOrder()->getBaseTotalDue());
                    $clonedInvoice = clone $invoice;
					//echo $capturePayment->canCapture();die;
                    $invoice->getOrder()->addRelatedObject($capturePayment);
                    if ($capturePayment->canCapture()) {
						
                        $capturePayment->capture($clonedInvoice);
						$paymentAdapter = $objectManager->create('Magento\Sales\Model\Order\PaymentAdapterInterface');
						$order = $paymentAdapter->pay($invoice->getOrder(), $clonedInvoice, true);
						$order->save();
                        //$capturePayment->pay($clonedInvoice);
                    } else {
                        $capturePayment->pay($clonedInvoice);
                    }
                } else {
                    throw new \Exception(__('Unable to save the invoice1.'));
					$this->resultRedirectFactory->create()->setPath('sales/order/view', array('order_id' => $this->getRequest()->getParam('order_id')));
                }
                //$invoice->register();
                $invoice->setEmailSent(true);
                $invoice->setState(2);
				$invoice->save();
                $invoice->getOrder()->setCustomerNoteNotify(true);
                $invoice->getOrder()->setIsCustomerNotified(true);
                $invoice->getOrder()->setIsInProcess(true);
                $transactionSave =$this->_transaction
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();
				$poHelper = $objectManager->create('Akeans\Pocustomize\Helper\Data');
				$poHelper->updatePoCredit($invoice->getOrder(),$invoice);
				$message = __('The invoice has been paid.');
				$this->messageManager->addSuccessMessage($message);
				$this->updateOrder($this->getRequest()->getParam('order_id'));
                try {
                  $this->invoiceSender->send($invoice);
                } catch (Exception $e) {
                   $this->messageManager->addErrorMessage(__('Unable to send the invoice email.'));
					return $this->redirect('sales/order/view/order_id/'.$this->getRequest()->getParam('order_id'));
                }
				return $this->redirect('sales/order/view/order_id/'.$this->getRequest()->getParam('order_id'));
            }
        } catch (\Exception $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
			return $this->redirect('sales/order/view/order_id/'.$this->getRequest()->getParam('order_id'));
        } catch (\Exception $e) {
			$this->messageManager->addErrorMessage(__('Unable to save the invoice.'.$e->getMessage()));
			return $this->redirect('sales/order/view/order_id/'.$this->getRequest()->getParam('order_id'));
        }
		return $this->redirect('sales/order/view/order_id/'.$this->getRequest()->getParam('order_id'));
    }
	public function updateOrder($orderId){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$sql = "Update sales_order_grid set payment_method = 'purchaseorder' where entity_id = ".$orderId;
		$connection->query($sql);
		return ;
	}
	public function redirect($path){
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath($path);
		return $resultRedirect;
	}
	
	protected function _initInvoice()
    {
        $orderId = $this->getRequest()->getParam('order_id');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$registery = $objectManager->create('\Magento\Framework\Registry');
		$customerSession = $objectManager->get('Magento\Customer\Model\Session');
		$order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
        if (!$order || !$order->getId() || ($order->getCustomerId() != $customerSession->getCustomer()->getId())) {
            $this->messageManager->addErrorMessage(__('The order no longer exists.'));
            return false;
        }
        if (!$order->canInvoice() || !$this->canMakePayment($order)) {
            $this->messageManager->addErrorMessage(__('The order does not allow creating an invoice.'));
            return false;
        }
		$invoice = $this->_invoiceService->prepareInvoice($order);
        if (!$invoice->getTotalQty()) {
            throw new \Exception(__('Cannot create an invoice without products.'));
        }
        $registery->register('current_invoice', $invoice);
        return $invoice;
    }
	protected function _validatePaymentMethod($order, $methodCode)
    {
        $_allowedMethods = $this->_getAllowedCaptureMethods($order);
        $methods = Mage::helper('emjainteractive_purchaseordermanagement/payment')->getCaptureMethods();
        foreach ($methods as $key => $method) {
            if (($method->getCode() == $methodCode)) {
                if (!in_array($methodCode, $_allowedMethods)) {
                    return false;
                }
                if (!$method->canUseForCountry($order->getBillingAddress()->getCountry())) {
                    return false;
                }
                if (!$method->canUseForCurrency(Mage::app()->getStore()->getBaseCurrencyCode())) {
                    return false;
                }
                $total = $order->getBaseGrandTotal();
                $minTotal = $method->getConfigData('min_order_total');
                $maxTotal = $method->getConfigData('max_order_total');
                if((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }
}
