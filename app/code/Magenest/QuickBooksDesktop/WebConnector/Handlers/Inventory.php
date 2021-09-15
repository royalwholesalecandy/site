<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\WebConnector\Handlers;

use Magenest\QuickBooksDesktop\Helper\GenerateTicket;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\TypeQuery;
use Magenest\QuickBooksDesktop\Model\CustomQueue as CustomQueue;
use Magenest\QuickBooksDesktop\Model\MappingFactory;
use Magenest\QuickBooksDesktop\Model\Ticket;
use Magenest\QuickBooksDesktop\Model\TaxFactory;
use Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\WebConnector;
use Magenest\QuickBooksDesktop\WebConnector\Receive\Response as ReceiveResponse;
use \Magento\Store\Model\StoreManagerInterface;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Version;
use Magento\Framework\App\Config\ScopeConfigInterface;
/**
 * Class Inventory
 * @package Magenest\QuickBooksDesktop\WebConnector\Handlers
 */
class Inventory extends WebConnector\Handlers
{
    /**
     * @var CustomQueue
     */
    public $customQueue;

    /**
     * @var TaxFactory
     */
    public $taxFactory;


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var customerAddressMapping
     */
    protected $customerAddressFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    protected $stockRegistry;

    /**
     * Customer constructor.
     * @param GenerateTicket $generateTicket
     * @param Ticket $ticket
     * @param CustomQueue $customQueue
     * @param ReceiveResponse $receiveResponse
     * @param ObjectManagerInterface $objectManager
     * @param WebConnector\Driver\Customer $driverCustomer
     * @param MappingFactory $mappingFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param TaxFactory $taxFactory
     * @param customerAddressMapping $customerAddressFactory
     */
    public function __construct(
        GenerateTicket $generateTicket,
        Ticket $ticket,
        CustomQueue $customQueue,
        ReceiveResponse $receiveResponse,
        ObjectManagerInterface $objectManager,
        WebConnector\Driver\Inventory $driverInventory,
        MappingFactory $mappingFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TaxFactory $taxFactory,
		\Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        QueueHelper $queueHelper
    ) {
        parent::__construct(
            $generateTicket,
            $ticket,
            $receiveResponse,
            $objectManager,
            $mappingFactory,
            $queueHelper
        );
        $this->_driver = $driverInventory;
        $this->customQueue = $customQueue;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
		$this->stockRegistry = $stockRegistry;
        $this->taxFactory = $taxFactory;
    }

    /**
     * @param $dataFromQWC
     */
    protected function processResponse($dataFromQWC)
    {
        try {
            $websites = $this->_storeManager->getWebsites();
            $websiteIds = [];
            foreach ($websites as $website) {
                $websiteIds [] = $website->getId();
            }
            $response = $this->getReceiveResponse();
            $response->setResponse($dataFromQWC);
            $result = $response->getValue();
// $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/responsecheck.log');
//     $logger = new \Zend\Log\Logger();
//     $logger->addWriter($writer);
//     $logger->info($result);
     //die;
            $iteratorId = $response->getIteratorId();

            $companyId = $this->_queueHelper->getCompanyId();

            \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug("ComeONnnn123123   " . print_r($result, true) . "\n");
            $data = array();
            $notFound = true;
            if (!empty($result['ItemInventoryRet'])) {
                foreach($result['ItemInventoryRet'] as $item){
					if (isset($item['Name'])) {
                        $data[] = array('sku' => $item['Name'], 'qty' => $item['QuantityOnHand'] - $item['QuantityOnSalesOrder']);
                        $notFound = false;
                    }
                    
                }
                if($notFound){
                    $data[] = array('sku' => $result['ItemInventoryRet']['Name'], 'qty' => $result['ItemInventoryRet']['QuantityOnHand'] - $result['ItemInventoryRet']['QuantityOnSalesOrder']);
                }
            }
			$this->updateStock($data);

            //$this->_driver->getCurrentQueue()->addData($data)->save();
    //   $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/inventoryRespose.log');
	// 		$logger = new \Zend\Log\Logger();
	// 		$logger->addWriter($writer);
	// 		$logger->info($result);
	// 		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/data.log');
	// 		$logger = new \Zend\Log\Logger();
	// 		$logger->addWriter($writer);
	// 		$logger->info($data);
			//$this->updateSystem();
        } catch (\Exception $exception) {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Psr\Log\LoggerInterface')
                ->debug($exception->getMessage());
        }
    }
	public function updateStock($data){
    // $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/newddar.log');
    // $logger = new \Zend\Log\Logger();
    // $logger->addWriter($writer);
    // $logger->info($data);
		if(!empty($data)){
			$i = 0;
			foreach($data as $d){
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->get('Magento\Catalog\Model\Product');
				if(isset($d['sku']) && $product->getIdBySku($d['sku']) && isset($d['qty'])){
				//$isInStock = false;
                //$Qty = 0;
                
                $sku = $d['sku'];
                $Qty = $d['qty'];
                $isInStock = true;
				// if($d['qty'] > 0){
				// 	$isInStock = true;
				// 	$Qty = $d['qty'];
				// }
				$stockItem = $this->stockRegistry->getStockItemBySku($sku);
					if($stockItem){
						$stockItem->setQty($Qty);
						$stockItem->setIsInStock($isInStock);
						$this->stockRegistry->updateStockItemBySku($sku, $stockItem);
					}
				}
			}

		}
	}

}
