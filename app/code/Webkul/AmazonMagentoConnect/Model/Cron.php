<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Model;

use Magento\Framework\App\Action\Context;
use Webkul\AmazonMagentoConnect\Helper\ProductOnAmazon as ProductToAmazonHelper;
use Magento\Framework\App\Action\Action;
use Webkul\AmazonMagentoConnect\Model\Accounts;
use Webkul\AmazonMagentoConnect\Helper\ManageOrderRawData;
use Webkul\AmazonMagentoConnect\Api\ProductMapRepositoryInterface;
use Magento\Framework\Session\SessionManager;

/**
 * custom cron actions
 */
class Cron
{
    /*
    contain amazon client object
    */
    public $amzClient;

    /**
     *
     * @param Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param ProductToAmazonHelper $productToAmazonHelper
     * @param Accounts $accounts
     * @param ManageOrderRawData $manageOrderRawData
     * @param \Webkul\AmazonMagentoConnect\Logger\Logger $logger
     * @param ProductMapRepositoryInterface $productMapRepo
     * @param SessionManager $coreSession
     * @param \Webkul\AmazonMagentoConnect\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        ProductToAmazonHelper $productToAmazonHelper,
        Accounts $accounts,
        ManageOrderRawData $manageOrderRawData,
        \Webkul\AmazonMagentoConnect\Logger\Logger $logger,
        ProductMapRepositoryInterface $productMapRepo,
        SessionManager $coreSession,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->productToAmazon = $productToAmazonHelper;
        $this->accounts = $accounts;
        $this->manageOrderRawData = $manageOrderRawData;
        $this->logger = $logger;
        $this->productMapRepo = $productMapRepo;
        $this->coreSession = $coreSession;
        $this->helper = $helper;
    }

    public function orderSyncFromAmazon()
    {
		/**
		 * 2019-12-24 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
		 * "Prevent the `Webkul_AmazonMagentoConnect` module from logging successful events":
		 * https://github.com/royalwholesalecandy/core/issues/64
		 */
        //$this->logger->info('============== cron exection started Now =================== ');
        $this->coreSession->setData('amz_cron', 'start');
        $result = [];
        try {
            $collection = $this->accounts->getCollection();
            foreach ($collection as $account) {
                $this->amzClient = $this->helper->getAmzClient($account->getId(), true);
                // check feed status
                $productMapColl = $this->productMapRepo
                            ->getCollectionByAccountId($account->getId());
                $productMapColl->addFieldToFilter('export_status', 0);
                $feedIds = $this->getFeedIds($productMapColl);
                $this->productToAmazon->checkProductFeedStatus($feedIds);
                // end check feed status
                // check amazon product by product api
                $productMapColl = $this->productMapRepo
                        ->getCollectionByAccountId($account->getId());
                $productMapColl->addFieldToFilter('export_status', 0);
                $amzProSku = $this->getProductSku($productMapColl);
                if (!empty($amzProSku)) {
                    $this->productToAmazon->checkProductStatusBySku($amzProSku);
                }
                
                // check amazon product by product api
				/**
				 * 2019-12-24 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
				 * "Prevent the `Webkul_AmazonMagentoConnect` module from logging successful events":
				 * https://github.com/royalwholesalecandy/core/issues/64
				 */
                //$this->logger->info(' Account id '.$account->getId());
                $orderParams['recordCount'] = '40';
                $dt = new \DateTime();
                
                $toDate = $dt->modify('-1 hour');
    
                $dtFrom = new \DateTime();
				// 2019-12-18 Dmitry Fedyuk https://github.com/mage2pro
				// You can set a custom value for testing.
                $fromDate = $dtFrom->modify('-1 day');

                $orderLists = $this->amzClient
                            ->listOrders($fromDate, $toDate, $orderParams['recordCount']);

				/**
				 * 2019-12-24 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
				 * "Prevent the `Webkul_AmazonMagentoConnect` module from logging successful events":
				 * https://github.com/royalwholesalecandy/core/issues/64
				 */
                //$this->logger->info(' order raw data ');
                //$this->logger->info(json_encode($orderLists));
                if (isset($orderLists['ListOrdersResult']['Orders']['Order'])) {
                    $amazonOrderArray = $orderLists['ListOrdersResult']['Orders']['Order'];
                    $amzOrders = isset($amazonOrderArray[0]) ? $amazonOrderArray : [0 => $amazonOrderArray];
                    $result = $this->manageOrderRawData->manageOrderData($amzOrders, $account->getId(), true);
                }
				/**
				 * 2019-12-24 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
				 * "Prevent the `Webkul_AmazonMagentoConnect` module from logging successful events":
				 * https://github.com/royalwholesalecandy/core/issues/64
				 */
                //$this->logger->info(' these Amazon order created ');
                //$this->logger->info(json_encode($result));
            }
        } catch (\Exception $e) {
            $this->logger->info('Model Cron OrderSyncFromAmazon : '.$e->getMessage());
        }
        $this->coreSession->setData('amz_cron', '');
		/**
		 * 2019-12-24 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
		 * "Prevent the `Webkul_AmazonMagentoConnect` module from logging successful events":
		 * https://github.com/royalwholesalecandy/core/issues/64
		 */
        //$this->logger->info('====================== cron exection finished================= ');
    }

    /**
     * get All feed ids
     */
    public function getProductSku($productMapColl)
    {
        $amzProSku = [];
        foreach ($productMapColl as $mappedProduct) {
            $amzProSku[] = $mappedProduct->getProductSku();
        }
        return array_unique($amzProSku);
    }

    /**
     * get All feed ids
     */
    public function getFeedIds($productMapColl)
    {
        $feedArray = [];
        foreach ($productMapColl as $feed) {
            $feedArray[] = $feed->getFeedsubmissionId();
        }
        return array_unique($feedArray);
    }
}
