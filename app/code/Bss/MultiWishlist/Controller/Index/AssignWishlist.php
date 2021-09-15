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

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Bss\MultiWishlist\Helper\Data as Helper;
use Magento\Wishlist\Helper\Data as HelperData;
use Bss\MultiWishlist\Model\WishlistLabelFactory;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Wishlist\Model\WishlistFactory;

class AssignWishlist extends \Magento\Wishlist\Controller\Index\Add
{
    /**
     * Config key 'Display Wishlist Summary'
     */
    const XML_PATH_WISHLIST_LINK_USE_QTY = 'wishlist/wishlist_link/use_qty';

    /** @var  \Magento\Framework\View\Result\Page */

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var WishlistLabelFactory
     */
    protected $wishlistLabel;

    /**
     * @var WishlistItem
     */
    protected $wishlistItem;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var WishlistFactory
     */
    protected $coreWishlist;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $helperData;

    /**
     * AssignWishlist constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param Helper $helper
     * @param WishlistLabelFactory $wishlistLabel
     * @param WishlistItem $wishlistItem
     * @param JsonFactory $resultJsonFactory
     * @param WishlistFactory $coreWishlist
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param Validator $formKeyValidator
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        Helper $helper,
        WishlistLabelFactory $wishlistLabel,
        WishlistItem $wishlistItem,
        JsonFactory $resultJsonFactory,
        WishlistFactory $coreWishlist,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        Validator $formKeyValidator,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        HelperData $helperData
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->layoutFactory = $layoutFactory;
        $this->helper = $helper;
        $this->wishlistLabel = $wishlistLabel;
        $this->wishlistItem = $wishlistItem;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->coreWishlist = $coreWishlist;
        $this->productMetadata = $productMetadata;
        $this->helperData = $helperData;
        parent::__construct($context, $customerSession, $wishlistProvider, $productRepository, $formKeyValidator);
    }

    /**
     * Assign item to wishlist group.
     *
     * @return $this|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if ($this->helper->isEnable()) {
            $var = $wishlist_ids = [];
            $wishlist_ids = isset($params['wishlist_id']) ? $params['wishlist_id'] : [0];
            $productId = isset($params['product']) ? (int)$params['product'] : null;
            $customerData = $this->_customerSession->getCustomer();

            if (!$productId || empty($wishlist_ids)) {
                $var["result"] = "error";
                 $var["message"] = '<div class="message-error error message"><div data-bind=\'html: message.text\'>' . __('Please try again.') . '</div></div>';
                return $this->resultJsonFactory->create()->setData($var);
            }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            $session = $this->_customerSession;

            $wishlistName = [];
            $wishlist = $this->coreWishlist->create()->loadByCustomerId($customerData->getId(), true);
            foreach ($wishlist_ids as $wishlistId) {
                try {
                    $product = $this->productRepository->getById($productId);
                } catch (NoSuchEntityException $e) {
                    $product = null;
                }
                if (!$product || !$product->isVisibleInCatalog()) {
                    $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
                    $resultRedirect->setPath('*/');
                    return $resultRedirect;
                }
                $buyRequest = new \Magento\Framework\DataObject($params);
                $result = $wishlist->addNewItem($product, $buyRequest, false, false, $wishlistId);
                if (is_string($result)) {
                    throw new \Magento\Framework\Exception\LocalizedException(__($result));
                }
                $wishlist->save();
                $wishlistName[] = $this->helper->getWishlistName($wishlistId);
                $this->_eventManager->dispatch(
                    'wishlist_add_product',
                    ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
                );
            }
            
            $this->messageManager->addSuccessMessage(__(
                    "%1 has been added to wish list %2.",
                    $product->getName(),
                    implode(',', $wishlistName)
                ));

            if ($session->getBeforeWishlistRequest()) {
                $session->unsBeforeWishlistRequest();
                $referer = $session->getBeforeWishlistUrl();
                if ($referer) {
                    $session->setBeforeWishlistUrl(null);
                } else {
                    $referer = $this->_redirect->getRefererUrl();
                }
                $resultRedirect->setPath('*', ['wishlist_id' => $wishlist->getId()]);
                return $resultRedirect;
            }

            if ($this->helper->isRedirect()) {
                $var["url"] = $this->_url->getUrl("wishlist");
            }
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
                return $this->resultJsonFactory->create()->setData($var);
            } else {
                $resultRedirect->setPath('*', ['wishlist_id' => $wishlist->getId()]);
                return $resultRedirect;
            }       
        } else {
            return parent::execute();
        }
    }
}
