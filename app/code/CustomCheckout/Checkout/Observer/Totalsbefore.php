<?php
namespace CustomCheckout\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;

class Totalsbefore implements ObserverInterface
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
 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
 $orderCollection = $objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Collection');
$order_Collection = $orderCollection->addAttributeToFilter('customer_id',$customer_id)
                    ->addFieldToSelect('created_at')
                    ->addAttributeToFilter('subtotal', array('gteq' => 250))
                    ->setPageSize(1)                    
                    ->setOrder('created_at', 'desc'); 
 
                    if(count($order_Collection)>0)
                     {
  
                         foreach($order_Collection as $order)
                        {
                         $LastOrderDate = $order->getCreatedAt();                    
                        }

                      $LastOrder = strtotime($LastOrderDate);
                       $NextTime = date("Y-m-d H:i:s", strtotime('-1 hours'));
                      $CheckTime = strtotime($NextTime);
 
                      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                      $cart = $objectManager->get('\Magento\Checkout\Model\Cart')->getQuote(); 
                      $subTotal = $cart->getSubtotal();
                      $store  =  $cart->getStoreId();
                      $carriers = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers');

                      /*lastorder date : 2019-01-19 10:32:43
					  	LastOrder : 1547893963
						next time : 2019-01-21 05:39:27
						check time : 1548049167
						*/

                      if($LastOrder>=$CheckTime && $subTotal<=250)
                      {
                        /*Update core_config_data Set value= 0 where path='carriers/dhl/active'
Update core_config_data Set value= 0 where path='carriers/fedex/active'
Update core_config_data Set value= 0 where path='carriers/flatrate/active'
Update core_config_data Set value=0 where path='carriers/tablerate/active'
Update core_config_data Set value= 0 where path='carriers/ups/active'
Update core_config_data Set value= 0 where path='carriers/usps/active'
Update core_config_data Set value= 0 where path='carriers/temando/active*/


                        $hiddenMethodCode   = 'freeshipping'; 

                         foreach ($carriers as $carrierCode => $carrierConfig) 
                        { 
                             $hiddenMethodCode   = 'freeshipping'; 
                           if( $carrierCode !=  $hiddenMethodCode)
                            {
 
                              //  $store->saveConfig("carriers/{$carrierCode}/active", '0');
                               
                                 $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                              $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                              $connection = $resource->getConnection();
                              $tableName = $resource->getTableName('core_config_data'); //gives table name with prefix
                               
                              //Update Data into table
                              $sql = "Update " . $tableName . " Set  value= 0 where path='carriers/{$carrierCode}/active'";
                             // echo 'in';
                             //  print_r($sql);  
                              $connection->query($sql); 

                              $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
							  $cacheManager = $objectManager->create('Magento\Framework\App\Cache\Manager');
							  $cacheManager->flush($cacheManager->getAvailableTypes());
                            }
                        }          
                      }else{   // < 72 && > 250 
                               // Update core_config_data Set value= 0 where path='carriers/freeshipping/active'    

                         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                      $cart = $objectManager->get('\Magento\Checkout\Model\Cart')->getQuote(); 
                      $subTotal = $cart->getSubtotal();
                      $store  =  $cart->getStoreId();
                      $carriers = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers');

                          $hiddenMethodCode   = 'freeshipping'; 

                          foreach ($carriers as $carrierCode => $carrierConfig) 
                          {
                            if( $carrierCode ==  $hiddenMethodCode)
                            {
                                  
                              $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                              $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                              $connection = $resource->getConnection();
                              $tableName = $resource->getTableName('core_config_data'); //gives table name with prefix
                               
                              //Update Data into table
                              $sql = "Update " . $tableName . " Set  value= 0 where path='carriers/{$carrierCode}/active'";
                              //  echo 'out';
                              // print_r($sql);
                               $connection->query($sql);

                              $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
							  $cacheManager = $objectManager->create('Magento\Framework\App\Cache\Manager');
							  $cacheManager->flush($cacheManager->getAvailableTypes());
                             }
                          }
                        } 

                       }else{  // Update core_config_data Set value= 0 where path='carriers/freeshipping/active'       
                             
                      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                      $cart = $objectManager->get('\Magento\Checkout\Model\Cart')->getQuote(); 
                      $subTotal = $cart->getSubtotal();
                      $store  =  $cart->getStoreId();
                      $carriers = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers');

                              $hiddenMethodCode   = 'freeshipping'; 

                              foreach ($carriers as $carrierCode => $carrierConfig) 
                              {
                                if( $carrierCode ==  $hiddenMethodCode)
                                {
 
                              //  $store->saveConfig("carriers/{$carrierCode}/active", '0');
                               
                              $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                              $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                              $connection = $resource->getConnection();
                              $tableName = $resource->getTableName('core_config_data'); //gives table name with prefix
                               
                              //Update Data into table
                              $sql = "Update " . $tableName . " Set  value= 0 where path='carriers/{$carrierCode}/active'";
                                 // echo 'out2';
                             // print_r($sql);  
                              $connection->query($sql);

                              $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
							  $cacheManager = $objectManager->create('Magento\Framework\App\Cache\Manager');
							  $cacheManager->flush($cacheManager->getAvailableTypes());
							  
                                }
                              }           
                            }
 
                    }   
  }  

}