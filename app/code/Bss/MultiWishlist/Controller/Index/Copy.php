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

use Magento\Framework\Controller\ResultFactory;
use Bss\MultiWishlist\Helper\Data as Helper;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\WishlistFactory;


class Copy extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var WishlistItem
     */
    protected $wishlistItem;

    /**
     * @var WishlistFactory
     */
    protected $coreWishlist;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var WishlistItem\OptionFactory
     */
    protected $optionFactory;

    /**
     * Copy constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param Helper $helper
     * @param WishlistItem $wishlistItem
     * @param WishlistFactory $coreWishlist
     * @param \Magento\Customer\Model\Session $customerSession
     * @param WishlistItem\OptionFactory $optionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Helper $helper,
        WishlistItem $wishlistItem,
        WishlistFactory $coreWishlist,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\Item\OptionFactory $optionFactory
    ) {
        $this->helper = $helper;
        $this->wishlistItem = $wishlistItem;
        $this->coreWishlist = $coreWishlist;
        $this->_customerSession = $customerSession;
        $this->optionFactory = $optionFactory;
        parent::__construct($context);
    }

    /**
     * Assign item to wishlist group.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $params = $this->getRequest()->getParams();

        $wishlistIds = isset($params['wishlist_id']) ? $params['wishlist_id'] : null;
        $itemId = isset($params['item']) ? (int)$params['item'] : null;
        $customerData = $this->_customerSession->getCustomer();
        $wishlist = $this->coreWishlist->create()->loadByCustomerId($customerData->getId(), true);
        
        if (!empty($wishlistIds) && $itemId) {
            foreach ($wishlistIds as $wishlistId) {
                $wlItem = $this->wishlistItem->load($itemId);
                $options = $this->optionFactory->create()->getCollection()->addItemFilter([$itemId]);
                $wlItem->setOptions($options->getOptionsByItem($itemId));
                $buyRequest = $wlItem->getBuyRequest();
                if ($wlItem->getWishlistItemId()) {
                    $product = $wlItem->getProduct();
                    $result = $wishlist->addNewItem($product, $buyRequest, false, false, $wishlistId);
                    $wishlist->save();
                }
                $wishlistName[] = $this->helper->getWishlistName($wishlistId);
            }

            $this->messageManager->addSuccessMessage(__(
                    "%1 has been copied to wish list %2.",
                    $product->getName(),
                    implode(',', $wishlistName)
                ));
        } else {
            $this->messageManager->addErrorMessage(__('Please try again.'));
        }
        $redirectUrl = $this->_redirect->getRedirectUrl($this->_url->getUrl('*/*'));
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }

}

