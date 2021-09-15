<?php
namespace Akeans\ShowPriceAfterLogin\Observer;
use Magento\Framework\Event\ObserverInterface;
class SalesOrderShipmentAfter implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
		$payment = $order->getPayment();
        $method = $payment->getMethodInstance();
		//echo $method->getCode();die;
        if($method->getCode() == 'purchaseorder'){
			$order->setState("processing")->setStatus("purchaseorder_pending_payment");
			$order->save();
		}
        // Do some things

    }
}