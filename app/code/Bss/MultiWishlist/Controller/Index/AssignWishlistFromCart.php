<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\Controller\Index;

use Bss\MultiWishlist\Helper\Data as Helper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Framework\Escaper;
use Magento\Framework\Controller\ResultFactory;

class AssignWishlistFromCart extends \Magento\Wishlist\Controller\Index\Fromcart
{

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var WishlistFactory
     */
    protected $coreWishlist;


    /**
     * AssignWishlistFromCart constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param WishlistHelper $wishlistHelper
     * @param CheckoutCart $cart
     * @param CartHelper $cartHelper
     * @param Escaper $escaper
     * @param Validator $formKeyValidator
     * @param Helper $helper
     * @param CustomerSession $customerSession
     * @param WishlistFactory $coreWishlist
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        WishlistProviderInterface $wishlistProvider,
        WishlistHelper $wishlistHelper,
        CheckoutCart $cart,
        CartHelper $cartHelper,
        Escaper $escaper,
        Validator $formKeyValidator,
        Helper $helper,
        CustomerSession $customerSession,
        WishlistFactory $coreWishlist
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->coreWishlist = $coreWishlist;
        parent::__construct($context, $wishlistProvider, $wishlistHelper, $cart, $cartHelper, $escaper,
            $formKeyValidator);

    }

    /**
     * Assign item to wishlist group.
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->helper->isEnable()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            $params = $this->getRequest()->getParams();
            $customerData = $this->customerSession->getCustomer();
            $itemId = isset($params['item']) ?(int)$params['item'] : null;
            $wishlistId = isset($params['wishlist_id']) ?(int)$params['wishlist_id'][0] : null;

            $item = $this->cart->getQuote()->getItemById($itemId);
            if (!$item) {
                $this->messageManager->addErrorMessage(__('The requested cart item doesn\'t exist.'));
            } else {
                $wishlist = $this->coreWishlist->create()->loadByCustomerId($customerData->getId(), true);
                $productId = $item->getProductId();
                $buyRequest = $item->getBuyRequest();
                $result = $wishlist->addNewItem($productId, $buyRequest, false, false, $wishlistId);
                $this->cart->getQuote()->removeItem($itemId);
                $this->cart->save();
                $wishlist->save();

                $this->messageManager->addSuccessMessage(__(
                    "%1 has been moved to your wish list.",
                    $this->escaper->escapeHtml($item->getProduct()->getName())
                ));
            }
            $redirectUrl = $this->_redirect->getRedirectUrl($this->_url->getUrl('*/*'));
            $resultRedirect->setUrl($redirectUrl);
            return $resultRedirect;
        } else {
            return parent::execute();
        }
    }

}
