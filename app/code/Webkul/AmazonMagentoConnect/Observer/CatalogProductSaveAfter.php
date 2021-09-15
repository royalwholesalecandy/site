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
use Webkul\AmazonMagentoConnect\Api\ProductMapRepositoryInterface;
use Magento\Framework\Session\SessionManager;

class CatalogProductSaveAfter implements ObserverInterface
{
    private $amzClient;

    /**
     * @var \Webkul\AmazonMagentoConnect\Model\Productmap
     */
    private $productMap;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItemRepository;

    /**
     * \Webkul\AmazonMagentoConnect\Helper\ProductOnAmazon
     */
    private $productOnAmazon;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     *
     * @param \Webkul\AmazonMagentoConnect\Logger\Logger $amzLogger
     * @param \Webkul\AmazonMagentoConnect\Model\ProductMap $productMap
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository
     * @param \Webkul\AmazonMagentoConnect\Helper\Data $helper
     * @param \Webkul\AmazonMagentoConnect\Helper\ProductOnAmazon $productOnAmazon
     * @param \Magento\Catalog\Model\Product $product
     */
    public function __construct(
        \Webkul\AmazonMagentoConnect\Logger\Logger $amzLogger,
        \Webkul\AmazonMagentoConnect\Model\ProductMap $productMap,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper,
        \Webkul\AmazonMagentoConnect\Helper\ProductOnAmazon $productOnAmazon,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SessionManager $coreSession
    ) {
        $this->logger = $amzLogger;
        $this->productMap = $productMap;
        $this->stockItemRepository = $stockItemRepository;
        $this->helper = $helper;
        $this->productOnAmazon = $productOnAmazon;
        $this->product = $product;
        $this->objectManager = $objectManager;
        $this->coreSession = $coreSession;
    }
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $backendSession = $this->objectManager->get(
            '\Magento\Backend\Model\Session'
        );
        $itemReviseStatus = $this->helper->getItemReviseStatus();
        $cronSession = $this->coreSession->getData('amz_cron');
        ;
        try {
            if (!$backendSession->getAmzSession() && !$cronSession && $itemReviseStatus) {
                $product = $observer->getProduct();
                $amzMappedProduct = $this->productMap->getCollection()
                                ->addFieldToFilter('magento_pro_id', ['eq'=>$product->getId()]);
                
                if ($accountId = $this->helper->getAmazonAccountId($amzMappedProduct)) {
                    $product = $this->product->load($product->getId());
                    $this->amzClient = $this->helper->getAmzClient($accountId);
                    //Update qty of amazon product
                    $updateQtyData[] = $this->productOnAmazon->updateQtyData($product);
    
                    //Update price of amazon product
                    $updatePriceData[] = $this->productOnAmazon->updatePriceData($product);
    
                    if (!empty($updateQtyData) && !empty($updatePriceData)) {
                        $this->_upateProductData($updateQtyData, $updatePriceData);
                    }
                }
            }
        } catch (\Execption $e) {
            $this->logger->info('Observer CatalogProductSaveAfter execute : '.$e->getMessage());
        }
    }

    /**
     * update amazon  product
     * @param  array $updateQtyData
     * @param  array $updatePriceData
     * @param  object $product
     */
    private function _upateProductData($updateQtyData, $updatePriceData)
    {
        try {
            $priceApiResponse = $this->amzClient->updatePrice($updatePriceData);
            /**
			 * 2019-12-24 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
			 * "Prevent the `Webkul_AmazonMagentoConnect` module from logging successful events":
			 * https://github.com/royalwholesalecandy/core/issues/64
			 */
            //$this->logger->info('== Observer CatalogProductSaveAfter priceApiResponse ==');
            //$this->logger->info(json_encode($priceApiResponse));

            $stockApiResponse = $this->amzClient->updateStock($updateQtyData);
            /**
			 * 2019-12-24 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
			 * "Prevent the `Webkul_AmazonMagentoConnect` module from logging successful events":
			 * https://github.com/royalwholesalecandy/core/issues/64
			 */
            //$this->logger->info('== Observer CatalogProductSaveAfter stockApiResponse ==');
            //$this->logger->info(json_encode($stockApiResponse));
        } catch (\Exception $e) {
            $this->logger->info('Observer CatalogProductSaveAfter _upateProductData : '.$e->getMessage());
        }
    }
}
