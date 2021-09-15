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
 * Class Orderprocessing
 * @package Magenest\QuickBooksDesktop\WebConnector\Handlers
 */
class Orderprocessing extends WebConnector\Handlers
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
        WebConnector\Driver\Orderprocessing $driverOrderprocessing,
        MappingFactory $mappingFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TaxFactory $taxFactory,
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
        $this->_driver = $driverOrderprocessing;
        $this->customQueue = $customQueue;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
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

            $iteratorId = $response->getIteratorId();

            $companyId = $this->_queueHelper->getCompanyId();

          \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug("ComeONnnn123123   " . print_r($result, true) . "\n");
          $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/orderprocessing.log');
          $logger = new \Zend\Log\Logger();
          $logger->addWriter($writer);
          $logger->info($result);
			//$this->updateSystem();
        } catch (\Exception $exception) {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Psr\Log\LoggerInterface')
                ->debug($exception->getMessage());
        }
    }
	/*public function updateSystem(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$value = 0;

		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$sql = "Update core_config_data Set value = ".$value." where path = 'qbdesktop/magento_sync/inventory'";
		$connection->query($sql);
		$_cacheTypeList = $objectManager->create('Magento\Framework\App\Cache\TypeListInterface');
		$_cacheFrontendPool = $objectManager->create('Magento\Framework\App\Cache\Frontend\Pool');
		$types = array('config');
		foreach ($types as $type) {
			$_cacheTypeList->cleanType($type);
		}
		foreach ($_cacheFrontendPool as $cacheFrontend) {
			$cacheFrontend->getBackend()->clean();
		}
	}*/
    public function saveTax($data)
    {
        $check = $this->taxFactory->create()->getCollection()->addFieldToFilter('list_id', $data['list_id'])->getFirstItem();
        if ($check->getId()) {
            $check->addData($data)->save();
        } else {
            $model = $this->taxFactory->create();
            $model->addData($data)->save();
        }
    }


    /**
     * check request
     */
    public function check()
    {
        $count = $this->_driver->getCollection()->getSize();
        if ($count == 0) {
            $companyId = $this->_queueHelper->getCompanyId();
            $model = $this->_objectManager->create('\Magenest\QuickBooksDesktop\Model\CustomQueue')->getCollection()
                ->addFieldToFilter('type', TypeQuery::QUERY_TAX)
                ->addFieldToFilter('company_id', $companyId);
            foreach ($model as $queue) {
                $queue->setStatus(Status::STATUS_QUEUE);
                $queue->save();
            }
        }
    }
}
