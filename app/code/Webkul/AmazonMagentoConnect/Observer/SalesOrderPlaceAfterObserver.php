<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    private $amzClient;

    /**
     * @var \Webkul\AmazonMagentoConnect\Model\Productmap
     */
    private $productMapRecord;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItemRepository;

    /**
     * @var \Webkul\AmazonMagentoConnect\Helper\Data
     */
    private $helper;

    /**
     * @var \Webkul\AmazonMagentoConnect\Logger\Logger
     */
    private $amzLogger;

    /**
     * @param \Webkul\AmazonMagentoConnect\Logger\Logger $amzLogger,
     * @param \Webkul\AmazonMagentoConnect\Model\Productmap $productMapRecord,
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
     */
    public function __construct(
        \Webkul\AmazonMagentoConnect\Logger\Logger $amzLogger,
        \Webkul\AmazonMagentoConnect\Model\ProductMap $productMapRecord,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper,
        \Webkul\AmazonMagentoConnect\Helper\ProductOnAmazon $productOnAmazon,
        \Magento\Catalog\Model\Product $product,
        SessionManager $coreSession,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->amzLogger = $amzLogger;
        $this->productMapRecord = $productMapRecord;
        $this->stockItemRepository = $stockItemRepository;
        $this->helper = $helper;
        $this->productOnAmazon = $productOnAmazon;
        $this->product = $product;
        $this->coreSession = $coreSession;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        try {
            $wholeItemData = [];
            $backendSession = $this->objectManager->get(
                '\Magento\Backend\Model\Session'
            );
            if (empty($this->coreSession->getData('amz_cron')) && !$backendSession->getAmzSession()) {
                $order = $observer->getOrder();
                $orderIncrementedId = $order->getIncrementId();
                $orderItems = $order->getAllItems();
                foreach ($orderItems as $item) {
                    $productId = $item->getProductId();
					/**
					 * 2019-12-24 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
					 * "Prevent the `Webkul_AmazonMagentoConnect` module from logging successful events":
					 * https://github.com/royalwholesalecandy/core/issues/64
					 */
                    //$this->amzLogger->info('observer SalesOrderPlaceAfterObserver
                    //                : Order quatity with product id - '.$productId);
                    $amzMappedProduct = $this->productMapRecord
                                        ->getCollection()
                                        ->addFieldToFilter('magento_pro_id', ['eq'=>$productId]);
                    if ($accountId = $this->helper->getAmazonAccountId($amzMappedProduct)) {
                        $this->amzClient = $this->helper->getAmzClient($accountId);
                        $product = $this->product->load($productId);
                        $updateQtyData = $this->productOnAmazon->updateQtyData($product);
                        $wholeItemData = $wholeItemData + $updateQtyData;
                    }
                }
                if (!empty($wholeItemData)) {
                    $stockApiResponse = $this->amzClient->updateStock($wholeItemData);
					/**
					 * 2019-12-24 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
					 * "Prevent the `Webkul_AmazonMagentoConnect` module from logging successful events":
					 * https://github.com/royalwholesalecandy/core/issues/64
					 */
                    //$this->amzLogger->info('== Observer SalesOrderPlaceAfterObserver stockApiResponse ==');
                    //$this->amzLogger->info(json_encode($stockApiResponse));
                }
            }
        } catch (\Exception $e) {
            $this->amzLogger->info('observer SalesOrderPlaceAfterObserver : '.$e->getMessage());
        }
    }
}
