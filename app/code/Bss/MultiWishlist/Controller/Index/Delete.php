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

use Magento\Framework\App\Action\Action;
use Bss\MultiWishlist\Helper\Data as Helper;
use Bss\MultiWishlist\Model\WishlistLabel;
use Magento\Wishlist\Model\Item as WishlistItem;

class Delete extends Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var WishlistLabel
     */
    protected $wishlistLabel;

    /**
     * @var WishlistItem
     */
    protected $wishlistItem;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * Delete constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param Helper $helper
     * @param WishlistLabel $wishlistLabel
     * @param WishlistItem $wishlistItem
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        Helper $helper,
        WishlistLabel $wishlistLabel,
        WishlistItem $wishlistItem
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->context = $context;
        $this->wishlistProvider = $wishlistProvider;
        $this->helper = $helper;
        $this->wishlistLabel = $wishlistLabel;
        $this->wishlistItem = $wishlistItem;
    }

    /**
     * Delete Wishlist Group.
     *
     * @return void
     */
    public function execute()
    {
        $mWishlistId = $this->getRequest()->getParam('mWishlistId');
        if (!$mWishlistId) {
            $this->messageManager->addErrorMessage(__('An error occurred, please try again later.'));
            $this->_redirect('wishlist');
        }
        $model = $this->wishlistLabel;
        $items = $this->helper->getWishlistItemsCollection($mWishlistId);
        $coreWishlistId = $items->getFirstItem()->getWishlistId();
        $coreWishlist = $this->wishlistProvider->getWishlist($coreWishlistId);
        foreach ($items as $item) {
            $this->wishlistItem->load($item->getId())->delete();
        };
        $model->load($mWishlistId)->delete();
        $coreWishlist->save();
        $this->messageManager->addSuccessMessage(__('The Wishlist has been deleted.'));
        $this->_redirect('wishlist/index/update', array('wishlist_id' => $coreWishlistId));
    }

}

