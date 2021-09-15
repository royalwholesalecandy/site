<?php
namespace Bss\AddMultipleProducts\Controller\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;
/**
 * 2019-12-14 Dmitry Fedyuk https://github.com/mage2pro
 * «Call to undefined method Bss\AddMultipleProducts\Controller\Cart\Add\Interceptor::goBack()
 * in app/code/Bss/AddMultipleProducts/Controller/Cart/Add.php:138»:
 * https://github.com/royalwholesalecandy/core/issues/32
 */
class Add extends \Magento\Checkout\Controller\Cart\Add
{

	/**
	 * @var \Magento\Framework\View\LayoutInterface
	 */
	protected $layout;

	/**
	 * @var \Magento\Framework\Filter\LocalizedToNormalized
	 */
	protected $localizedToNormalized;

	/**
	 * @var \Magento\Framework\Controller\Result\JsonFactory
	 */
	protected $resultJsonFactory;

	/**
	 * Add constructor.
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Magento\Framework\View\LayoutInterface $layout
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
	 * @param \Magento\Framework\Filter\LocalizedToNormalized $localizedToNormalized
	 * @param \Magento\Checkout\Model\Cart $cart
	 * @param ProductRepositoryInterface $productRepository
	 * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	 */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\LayoutInterface $layout,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
		\Magento\Framework\Filter\LocalizedToNormalized $localizedToNormalized,
		CustomerCart $cart,
		ProductRepositoryInterface $productRepository,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	) {
		parent::__construct(
			$context,
			$scopeConfig,
			$checkoutSession,
			$storeManager,
			$formKeyValidator,
			$cart,
			$productRepository
		);
		$this->layout = $layout;
		$this->localizedToNormalized = $localizedToNormalized;
		$this->resultJsonFactory = $resultJsonFactory;
	}

	/**
	 * @return bool|\Magento\Catalog\Api\Data\ProductInterface
	 */
	protected function _initProduct()
	{
		$productId = (int)$this->getRequest()->getParam('product');
		if ($productId) {
			$storeId = \Magento\Framework\App\ObjectManager::getInstance()->get(
				\Magento\Store\Model\StoreManagerInterface::class
			)->getStore()->getId();
			try {
				return $this->productRepository->getById($productId, false, $storeId);
			} catch (NoSuchEntityException $e) {
				return false;
			}
		}
		return false;
	}

	/**
	 * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	public function execute()
	{
		/** @var \Magento\Framework\Controller\Result\Json $resultJson */
		$resultJson = $this->resultJsonFactory->create();

		$info_mn_cart = [];
		$before_cart = $this->cart;
		$info_mn_cart['qty'] = $before_cart->getItemsQty();
		$info_mn_cart['subtotal'] = $before_cart->getQuote()->getSubtotal();
		$params = $this->getRequest()->getParams();
		$result = [];
		try {
			if (isset($params['qty'])) {
				$filter = $this->localizedToNormalized->setOptions(
					['locale' => \Magento\Framework\App\ObjectManager::getInstance()->get(
						\Magento\Framework\Locale\ResolverInterface::class
					)->getLocale()]
				);
				$params['qty'] = $filter->filter($params['qty']);
			}

			$product = $this->_initProduct();
			$related = $this->getRequest()->getParam('related_product');

			if (!$product) {
				return $this->goBack();
			}

			$this->cart->addProduct($product, $params);

			$relatedAdded = false;
			if (!empty($related)) {
				$relatedAdded = true;
				$this->cart->addProductsByIds(explode(',', $related));
			}

			$this->cart->save();

			/**
			 * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
			 */
			$this->_eventManager->dispatch(
				'checkout_cart_add_product_complete',
				['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
			);

			if (!$this->_checkoutSession->getNoCartRedirect(true)) {
				if (!$this->cart->getQuote()->getHasError()) {
					$after_cart = $this->cart;
					$info_mn_cart['qty'] = $after_cart->getItemsQty();
					$info_mn_cart['subtotal'] = $after_cart->getQuote()->getSubtotal();
					$cartItem = $this->cart->getQuote()->getItemByProduct($product);
					$html = $this->getContentPopup($product, $info_mn_cart, $cartItem);
					$message = __(
						'You added %1 to your shopping cart.',
						$product->getName()
					);
					$result['popup'] = $html;
					$this->messageManager->addSuccessMessage($message);
					return $resultJson->setData($result);
				}
			}
		} catch (\Magento\Framework\Exception\LocalizedException $e) {
			if ($this->_checkoutSession->getUseNotice(true)) {
				$this->messageManager->addNotice(
					\Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
				);
				$product_fail[$product->getId()] = ['qty'=> $params['qty'],'mess'=>$e->getMessage()];
			} else {
				$messages = array_unique(explode("\n", $e->getMessage()));
				foreach ($messages as $message) {
					$this->messageManager->addError(
						\Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Escaper')->escapeHtml($message)
					);
				}
				$product_fail[$product->getId()] = ['qty'=> $params['qty'],'mess'=>end($messages)];
			}

			$product_poup['errors'] = $product_fail;
			$html = $this->getContentPopup($product_poup, $info_mn_cart);
			$message = __(
				'You added %1 to your shopping cart.',
				$product->getName()
			);
			$result['popup'] = $html;
			return $resultJson->setData($result);
		} catch (\Exception $e) {
			$this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'.$e->getMessage()));
			\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->critical($e);
			return $resultJson->setData($result);
		}
	}

	/**
	 * @param $product
	 * @param $info_mn_cart
	 * @param null $cartItem
	 * @return mixed
	 */
	private function getContentPopup($product, $info_mn_cart, $cartItem = null)
	{
		$template = 'Bss_AddMultipleProducts::popup.phtml';
		$html = $this->layout
						->createBlock('Bss\AddMultipleProducts\Block\OptionProduct')
						->setTemplate($template)
						->setProduct($product)
						->setCart($info_mn_cart)
						->setTypeadd('single');
		if ($cartItem) {
			$html->setPrice($cartItem->getPrice());
		}

		return $html->toHtml();
	}
}
