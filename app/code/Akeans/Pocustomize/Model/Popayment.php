<?php

/**

 * Copyright © Magento, Inc. All rights reserved.

 * See COPYING.txt for license details.

 */

namespace Akeans\Pocustomize\Model\Order;

use Magento\Framework\Exception\LocalizedException;

use Magento\Framework\App\ObjectManager;

use Magento\Framework\Pricing\PriceCurrencyInterface;

use Magento\Sales\Api\Data\OrderPaymentInterface;

use Magento\Sales\Api\OrderRepositoryInterface;

use Magento\Sales\Model\Order;

use Magento\Sales\Model\Order\Payment\Info;

use Magento\Sales\Model\Order\Payment\Transaction;

use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;

use Magento\Sales\Api\CreditmemoManagementInterface as CreditmemoManager;

/**

 * Order configuration model

 *

 * @api

 * @since 100.0.2

 */

class Popayment extends\Magento\Sales\Model\Order\Payment

{

	/**

     * Declare order model object

     *

     * @codeCoverageIgnore

     *

     * @param Order $order

     * @return $this

     */

	public
	function setOrder( Order $order )

	{

		$this->_order = $order;



		return $this;

	}

	protected
	function _lookupTransaction( $txnId, $txnType = false )

	{

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		if ( !$txnId ) {

			if ( $txnType && $this->getId() ) {





				$collection = $objectManager->create( '\Magento\Sales\Model\Order\Payment\Transaction' )->getCollection()

				->setOrderFilter( $this->getOrder() )

				->addPaymentIdFilter( $this->getId() )

				->addTxnTypeFilter( $txnType );



				foreach ( $collection as $txn ) {

					$txn->setOrderPaymentObject( $this );

					$this->_transactionsLookup[ $txn->getTxnId() ] = $txn;

					return $txn;

				}

			}

			return false;

		}

		if ( isset( $this->_transactionsLookup[ $txnId ] ) ) {

			return $this->_transactionsLookup[ $txnId ];

		}

		$txn = $objectManager->create( '\Magento\Sales\Model\Order\Payment\Transaction' )

		->setOrderPaymentObject( $this )

		->loadByTxnId( $txnId );

		if ( $txn->getId() ) {

			$this->_transactionsLookup[ $txnId ] = $txn;

		} else {

			$this->_transactionsLookup[ $txnId ] = false;

		}

		return $this->_transactionsLookup[ $txnId ];

	}

	public
	function capture( $invoice = null )

	{

		if ( is_null( $invoice ) ) {

			$invoice = $this->_invoice();

			$this->setCreatedInvoice( $invoice );

			return $this; // @see Mage_Sales_Model_Order_Invoice::capture()

		}

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$amountToCapture = $this->formatAmount( $invoice->getBaseGrandTotal() );

		$order = $this->getOrder();



		// prepare parent transaction and its amount

		$paidWorkaround = 0;

		if ( !$invoice->wasPayCalled() ) {

			$paidWorkaround = ( float )$amountToCapture;

		}

		$this->isCaptureFinal( $paidWorkaround );



		if ( !$this->getParentTransactionId() ) {

			//$transaction = $objectManager->create('\Magento\Sales\Model\Order\Payment\Transaction');

			$orderingTransaction = $this->getParentTransaction();

			if ( $orderingTransaction ) {

				$this->setParentTransactionId( $orderingTransaction->getTxnId() );

			}

		}





		/*$this->transactionManager->generateTransactionId($this, 

		    \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,

		    $this->getAuthorizationTransaction()

		);*/

		$eventManager = $objectManager->create( '\Magento\Framework\Event\Manager' );

		$eventManager->dispatch( 'sales_order_payment_capture', array( 'payment' => $this, 'invoice' => $invoice ) );

		//Mage::dispatchEvent('sales_order_payment_capture', array('payment' => $this, 'invoice' => $invoice));



		/**

		 * Fetch an update about existing transaction. It can determine whether the transaction can be paid

		 * Capture attempt will happen only when invoice is not yet paid and the transaction can be paid

		 */

		$manager = $objectManager->get( 'Magento\Store\Model\StoreManagerInterface' );

		$store = $manager->getStore( $order->getStoreId() );

		if ( $invoice->getTransactionId() ) {

			$this->getMethodInstance()

			->setStore( $store )

			->fetchTransactionInfo( $this, $invoice->getTransactionId() );

		}



		$status = true;

		//echo $invoice->getIsPaid().'---'.$this->getIsTransactionPending();die;

		if ( !$invoice->getIsPaid() && !$this->getIsTransactionPending() ) {



			// attempt to capture: this can trigger "is_transaction_pending"

			//echo $amountToCapture;die;

			//$this->setAmountAuthorized($amountToCapture);

			//$this->setBaseAmountAuthorized($amountToCapture);

			//$this->capture('null');

			$method = $this->getMethodInstance();



			//TODO replace for sale usage

			try {

				$method->capture( $this, $amountToCapture );

			} catch ( \Exception $e ) {

				$this->redirect( 'sales/order/view/order_id/' . $order->getId(), $e->getMessage() );

			}

			//$this->getMethodInstance()->capture($this, $amountToCapture);

			//echo 'test';die;

			//$this->orderPaymentProcessor->capture($this, $invoice);

			$transaction = $this->addTransaction(

				\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,

				$invoice,

				true

			);



			if ( $this->getIsTransactionPending() ) {

				$message = __( 'Capturing amount of %s is pending approval on gateway.', $this->formatAmount( $amountToCapture ) );

				$message1 = __( 'Capturing amount of %s is pending approval on gateway.', $this->formatAmount( $amountToCapture ) );

				$state = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;

				if ( $this->getIsFraudDetected() ) {

					$status = \Magento\Sales\Model\Order::STATUS_FRAUD;

				}

				$invoice->setIsPaid( false );
				
				//echo 'testfalse';die;

			} else { // normal online capture: invoice is marked as "paid"

				$message = 'Credit Card: xxxx-' . $method->getCcLast4() . ' amount $' . $this->formatAmount( $amountToCapture ) . ' authorize and capture - successful. Authorize.Net Transaction ID ' . $invoice->getTransactionId() . '. for invoice No.: "' . $invoice->getIncrementId() . '".';

				$message1 = __( 'Captured amount of ' . $this->formatAmount( $amountToCapture ) . ' online.' );

				$state = \Magento\Sales\Model\Order::STATE_COMPLETE;
				$status = \Magento\Sales\Model\Order::STATE_COMPLETE;
				
				$invoice->setIsPaid( true );

				$this->_updateTotals( array( 'base_amount_paid_online' => $amountToCapture ) );
				//echo 'paid';die;

			}



			$message = $this->prependMessage( $message );

			$message = $this->_appendTransactionToMessage( $transaction, $message );

			// }


			$order->setCustomerNoteNotify( true );

			$order->setIsCustomerNotified( true );

			$order->setState( $state, $status, $message );

			//$message = __('Captured amount of $'.$this->formatAmount($amountToCapture).' online.');
			
			$order

				->addStatusHistoryComment(

				$message,

				$status

			)->setIsCustomerNotified( $invoice->getOrder()->getCustomerNoteNotify() );
			$order->setPaymentMethod( 'purchaseorder' );

			// $this->getMethodInstance()->processInvoice($invoice, $this); // should be deprecated

			return $this;

		}

		$this->redirect( 'sales/order/view/order_id/' . $order->getId(), __( 'The transaction "%s" cannot be captured yet.', $invoice->getTransactionId() ) );

	}

	public
	function redirect( $path, $message ) {

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$urlManager = $objectManager->create( '\Magento\Framework\UrlInterface' );

		$_messageManager = $objectManager->create( '\Magento\Framework\Message\ManagerInterface' );

		$_messageManager->addError( $message );

		$_responseFactory = $objectManager->create( '\Magento\Framework\App\ResponseFactory' );

		$CustomRedirectionUrl = $urlManager->getUrl( $path );

		$_responseFactory->create()->setRedirect( $CustomRedirectionUrl )->sendResponse();

		exit();

	}

	public
	function importData( array $data, $store )

	{



		$data = new\Magento\Framework\DataObject( $data );

		$this->setMethod( $data->getMethod() );

		$method = $this->getMethodInstance();

		$this->setCcNumber( $data->getCcNumber() );

		$this->setCcLast4( substr( $data->getCcNumber(), -4 ) );

		$this->setCcType( $data->getCcType() );

		$this->setCcExpMonth( $data->getCcExpMonth() );

		$this->setCcExpYear( $data->getCcExpYear() );

		if ( $data->getCcCid() ) {

			$this->setCcCid( $data->getCcCid() );

		}

		$method->validate();

		if ( $data->getMethod() == 'checkmo' ) {

			if ( $data->getPoNumber() ) {

				$this->setPoNumber( $data->getPoNumber() );

			}

		}

		return $this;

	}
	public function markAsPaid($invoice){
		
		$invoice->setIsPaid( true );
		$amountToCapture = $this->formatAmount( $invoice->getBaseGrandTotal() );
		$this->_updateTotals( array( 'base_amount_paid_online' => $amountToCapture ) );
		$order= $invoice->getOrder();
		$state = \Magento\Sales\Model\Order::STATE_COMPLETE;
		$status = \Magento\Sales\Model\Order::STATE_COMPLETE;
		$message = __( '#'.$invoice->getIncrementId().' Invoice marked as paid from admin.' );
		$order->addStatusHistoryComment($message, $status);
		$order->setCustomerNoteNotify( true );
		$order->setIsCustomerNotified( true );
		$order->setState( $state, $status, $message );
		$order->setTotalPaid($order->getTotalPaid() + $invoice->getGrandTotal());
     	$order->setBaseTotalPaid($order->getBaseTotalPaid() + $invoice->getBaseGrandTotal());
		$order->save();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		foreach($invoice->getAllItems() as $item){
			$orderItem = $objectManager->create('\Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
			$orderItem->setQtyInvoiced($item->getQty())->save();
		}
	}
}