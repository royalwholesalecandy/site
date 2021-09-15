<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\AmazonMagentoConnect\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
	/*
	StoreManagerInterface
	 */
	private $storeManager;

	/*
	\Magento\Catalog\Model\Product
	 */
	private $product;

	/*
	StockRegistryInterface
	 */
	private $stockRegistry;

	/*
	\Webkul\AmazonMagentoConnect\Logger\Logger
	 */
	private $logger;

	/*
	\Magento\Sales\Model\Order
	 */
	private $order;

	/*
	\Magento\Quote\Model\Quote\Address\Rate
	 */
	private $shippingRate;

	/*
	\Magento\Quote\Api\CartManagementInterface
	 */
	private $cartManagementInterface;

	/*
	\Magento\Quote\Api\CartRepositoryInterface
	 */
	private $cartRepositoryInterface;

	/*
	\Magento\Backend\Model\Session
	 */
	private $backendSession;

	/*
	\Magento\Customer\Api\CustomerRepositoryInterface
	 */
	private $customerRepository;

	/*
	\Magento\Customer\Model\CustomerFactory
	 */
	private $customerFactory;

	/*
	\Webkul\AmazonMagentoConnect\Helper\Data
	 */
	private $helper;

	/**
	 * @param \Magento\Framework\App\Helper\Context             $context
	 * @param StoreManagerInterface                             $storeManager
	 * @param \Magento\Catalog\Model\Product                    $product
	 * @param StockRegistryInterface                            $stockRegistry
	 * @param \Magento\Customer\Model\CustomerFactory           $customerFactory
	 * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
	 * @param \Magento\Backend\Model\Session                    $backendSession
	 * @param \Webkul\AmazonMagentoConnect\Logger\Logger        $logger
	 * @param \Magento\Quote\Api\CartRepositoryInterface        $cartRepositoryInterface
	 * @param \Magento\Quote\Api\CartManagementInterface        $cartManagementInterface
	 * @param \Magento\Quote\Model\Quote\Address\Rate           $shippingRate
	 * @param \Magento\Sales\Model\Order                        $order
	 * @param \Webkul\AmazonMagentoConnect\Helper\Data          $helper
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		StoreManagerInterface $storeManager,
		\Magento\Directory\Model\CurrencyFactory $currencyFactory,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		StockRegistryInterface $stockRegistry,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
		\Magento\Backend\Model\Session $backendSession,
		\Webkul\AmazonMagentoConnect\Logger\Logger $logger,
		\Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
		\Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
		\Magento\Quote\Model\Quote\Address\Rate $shippingRate,
		\Magento\Sales\Model\Order $order,
		\Webkul\AmazonMagentoConnect\Helper\Data $helper
	) {
		$this->storeManager = $storeManager;
		$this->productFactory = $productFactory;
		$this->stockRegistry = $stockRegistry;
		$this->logger = $logger;
		$this->customerFactory = $customerFactory;
		$this->customerRepository = $customerRepository;
		$this->backendSession = $backendSession;
		$this->cartRepositoryInterface = $cartRepositoryInterface;
		$this->cartManagementInterface = $cartManagementInterface;
		$this->shippingRate = $shippingRate;
		$this->order = $order;
		$this->helper = $helper;
		$this->currencyFactory = $currencyFactory;
		parent::__construct($context);
	}

	/**
	 * create amazon order on magento
	 * @param  array $orderData
	 * @return array
	 */
	public function createAmazonOrderAtMage($orderData)
	{
		$productNameError = '';
		try {
			if (!$orderData['shipping_address']['street'] || !$orderData['shipping_address']['country_id']) {
				return ['error' => 1,'msg' => __('order id ').$orderData['amz_order_id'].__(' not contain address')];
			}

			$storeId = $this->helper->getDefaultStoreOrderSync();
			$store = $this->storeManager->getStore($storeId);
			$websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
			$customer = $this->customerFactory->create();
			$customer->setWebsiteId($websiteId);
			$customer->loadByEmail($orderData['email']);

			if (!$customer->getEntityId()) {
				$customer->setWebsiteId($websiteId)
						->setStore($store)
						->setFirstname($orderData['shipping_address']['firstname'])
						->setLastname($orderData['shipping_address']['lastname'])
						->setEmail($orderData['email'])
						->setPassword($orderData['email']);
				$customer->save();
			}

			$cartId = $this->cartManagementInterface->createEmptyCart();
			$quote = $this->cartRepositoryInterface->get($cartId);

			$quote->setStore($store);

			$amazonCurrency =  $this->currencyFactory->create()->load($orderData['currency_id']);

			$this->storeManager->getStore($storeId)->setCurrentCurrency($amazonCurrency);

			$customer = $this->customerRepository->getById($customer->getEntityId());

			$quote->setCurrency();
			$quote->assignCustomer($customer);
			if (!empty($orderData['items'])) {
				foreach ($orderData['items'] as $item) {
					$product = $this->productFactory->create()->load($item['product_id']);
					/**
					 * 2019-12-26 Dmitry Fedyuk https://github.com/mage2pro
					 * "`Webkul_AmazonMagentoConnect`: «Product that you are trying to add is not available»":
					 * https://github.com/royalwholesalecandy/core/issues/75
					 * @see \Magento\Quote\Model\Quote::addProduct():
					 *		if (!$product->isSalable()) {
					 *			throw new \Magento\Framework\Exception\LocalizedException(
					 *				__('Product that you are trying to add is not available.')
					 *			);
					 *		}
					 * https://github.com/magento/magento2/blob/2.3.3/app/code/Magento/Quote/Model/Quote.php#L1626-L1630
					 */
					if (!$product->isSalable()) {
						df_log_l($this, "The product is not available: {$item['product_id']}", true);
					}
					else {
						$productNameError = $productNameError .' '. $product->getName().'( SKU : '.$product->getSku().')';
						$product->setPrice($item['price']);
						$quote->addProduct(
							$product,
							(int)$item['qty']
						);
					}
				}
			} else {
				$result = [
					'error' => 1,
					'msg' => __('order id ').$orderData['amz_order_id'].__(' not created on your store')
				];
				return $result;
			}

			//Set Address to quote
			$quote->getBillingAddress()->addData($orderData['shipping_address']);
			$quote->getShippingAddress()->addData($orderData['shipping_address']);

			// Collect Rates and Set Shipping & Payment Method
			$shipmethod = 'wk_amzconnectship_wk_amzconnectship';
			// Collect Rates and Set Shipping & Payment Method
			$this->shippingRate
				->setCode('wk_amzconnectship_wk_amzconnectship')
				->getPrice(1);

			//store shipping data in session
			$this->backendSession->setAmzShipDetail($orderData['shipping_service']);
			$shippingAddress = $quote->getShippingAddress();
			$shippingAddress->setCollectShippingRates(true)
							->collectShippingRates()
							->setShippingMethod('wk_amzconnectship_wk_amzconnectship');
			$quote->getShippingAddress()->addShippingRate($this->shippingRate);

			$quote->setPaymentMethod('amzpayment');
			$quote->setInventoryProcessed(false);

			/**
			 * 2019-12-18 Dmitry Fedyuk https://github.com/mage2pro
			 * `Webkul_AmazonMagentoConnect`:
			 * «... is out of stock. please increase the stock to create order» /
			 * «The requested Payment Method is not available»:
			 * https://github.com/royalwholesalecandy/core/issues/49
			 */
			df_store_m()->setCurrentStore($storeId);
			// Set Sales Order Payment
			$quote->getPayment()->importData(['method' => 'amzpayment']);

			$quote->save();
			// Collect Totals & Save Quote
			$quote->collectTotals();
			// Create Order From Quote
			$quote = $this->cartRepositoryInterface->get($quote->getId());
			$orderId = $this->cartManagementInterface->placeOrder($quote->getId());
			$order = $this->order->load($orderId);

			$orderStatus = $this->helper->getOrderStatus($orderData['order_status']);
			$order->setStatus($orderStatus)
				->setState($orderStatus)
				->setCreatedAt($orderData['purchase_date'])->save();
			$order->setEmailSent(0);
			$incrementId = $order->getRealOrderId();
			// Resource Clean-Up
			$quote = $customer = $service = null;
			if ($order->getEntityId()) {
				$result['order_id'] = $order->getRealOrderId();
			} else {
				$result = [
					'error' => 1,
					'msg' => __('order id ').$orderData['amz_order_id'].__(' not created on your store')
				];
			}
			return $result;
		} catch (\Exception $e) {
			/**
			 * 2019-12-16 Dmitry Fedyuk https://github.com/mage2pro
			 * `Webkul_AmazonMagentoConnect`: «... is out of stock. please increase the stock to create order» /
			 * «The requested Payment Method is not available»:
			 * https://github.com/royalwholesalecandy/core/issues/49
			 */
			df_log_l($this, $e->getMessage() . "\n" . $e->getTraceAsString(), true);
			$errorMsg = empty($productNameError) ? $e->getMessage() : $productNameError. ' is out of stock. please increase the stock to create order.';
			$result = [
				'error' => 1,
				'msg' => $errorMsg,
				'actual_error'  => $e->getMessage(),
				'product_ids' => json_encode($orderData['items'])
			];
			return $result;
		}
	}
}
