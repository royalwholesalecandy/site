<?php
 namespace CustomCheckout\Checkout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
 
 
class Maxorder implements ObserverInterface
{
    /**
     * custom event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    
 public function execute(\Magento\Framework\Event\Observer $observer)
    { 

$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$customerSession = $objectManager->create('Magento\Customer\Model\Session');
if ($customerSession->isLoggedIn()) {
    $customer_id = $customerSession->getCustomer()->getId(); 
    //echo 'Customer Id: ' . $customerSession->getCustomer()->getId() . '<br/>';
   
 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
 $orderCollection = $objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Collection');
$order_Collection = $orderCollection->addAttributeToFilter('customer_id',$customer_id)
                    ->addFieldToSelect('created_at')
                    ->addAttributeToFilter('subtotal', array('gteq' => 8))
                    ->setPageSize(1)                    
                    ->setOrder('created_at', 'desc'); 
            if(count($order_Collection)>0)
              {
                  foreach($order_Collection as $order)
                  {
                   $LastOrderDate = $order->getCreatedAt();                    
                  }

                  $LastOrder = strtotime($LastOrderDate);
                  $NextTime = date("Y-m-d H:i:s", strtotime('-77 hours'));
                  $CheckTime = strtotime($NextTime);
               
                  if($LastOrder>=$CheckTime)
                  {
                   // echo "Order done in last 3 days";
                  }else{
              
                      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                      $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
                      $subTotal = $cart->getQuote()->getSubtotal();
                      $minimum_order =  Mage::getStoreConfig('sales/minimum_order/amount');
                    if($subTotal < $minimum_order)
                    {
                        $errorMessage = "Minimum order amount of $".$minimum_order." to order.";
                        $this->messageManager->addError($errorMessage);

                        $redirectionUrl = $this->url->getUrl('checkout/cart');
                        $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
                          exit;                  
                    }
             
                  }   
            }else{
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
                     
                    $subTotal = $cart->getQuote()->getSubtotal();
  
                    $minimum_order =  Mage::getStoreConfig('sales/minimum_order/amount');
                    if($subTotal < $minimum_order)
                    {
                        $errorMessage = "Minimum order amount of $".$minimum_order." to order";
                         $this->messageManager->addError($errorMessage);
                         $redirectionUrl = $this->url->getUrl('checkout/cart');
                        $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
                          exit;
                    }
                }
        }
 
  }
 }