<?php

namespace Amasty\Checkout\Observer;

use Magento\Framework\Event\Observer;

use Magento\Framework\Event\ObserverInterface;

use Magento\Sales\Model\Order;

use Magento\Sales\Model\Order\Address;

use Magento\Framework\App\ResourceConnection;

class changeOrderId implements ObserverInterface

{

	protected $scopeConfig;

	protected $resource;

	protected $logger;

	protected $OrderidobjFactory;

	public function __construct(

		\Psr\Log\LoggerInterface $logger,

		ResourceConnection $resource,

        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig

    ) {

    	$this->resource = $resource;

    	$this->logger = $logger;

        $this->scopeConfig = $scopeConfig;

        //$this->UsedorderidobjFactory = $Usedorderidobj;

    }

    public function execute(Observer $observer)

    {

        $orderInstance = $observer->getOrder();

        $methodIdentifire = '';

        $shippingMethod = $orderInstance->getShippingMethod();

       /* $writer = new \Zend\Log\Writer\Stream(BP.'/var/log/akeans.log');

        $logger = new \Zend\Log\Logger();

        $logger->addWriter($writer);

        $logger->info($shippingMethod);*/

		$prefix = '';

        if($shippingMethod){

            $methodString = explode('_', $shippingMethod);

            $methodIdentifire = strtolower($methodString[0]);

        }

        if($methodIdentifire == 'ups'){

            $prefix = 'U_';

        }else if($methodIdentifire == 'fedex'){

            $prefix = 'F_';

        }else if($methodIdentifire == 'usps'){

            $prefix = 'USPS_';

        }else{

			if($orderInstance->getCustomerGroupId()){

				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

				$groupFactory = $objectManager->create('\Magento\Customer\Model\GroupFactory');

				$group = $groupFactory->create();

				$gData = $group->load($orderInstance->getCustomerGroupId());

				if($gData->getData('order_prefix')){

					$prefix = $gData->getData('order_prefix').'_';

				}

			}else{

				$prefix = '';

			}

        }

        $id = $orderInstance->getIncrementId();

		$idArray = explode('_', $id);

		if(is_array($idArray)){

			$id = end($idArray);

		}

        if($prefix){

            $orderInstance->setData("increment_id",$prefix.$id)->save();

        }

    }

}
