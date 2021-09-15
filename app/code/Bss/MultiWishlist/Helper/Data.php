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
namespace Bss\MultiWishlist\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Bss\MultiWishlist\Model\WishlistLabel;
use Bss\MultiWishlist\Model\ResourceModel\WishlistLabel\CollectionFactory;
use Magento\Wishlist\Model\Wishlist;

class Data extends AbstractHelper
{

    const XML_PATH_ENABLED = 'bss_multiwishlist/general/enable';
    const XML_PATH_REMOVE_ITEM_ADDCART = 'bss_multiwishlist/general/remove_item_addcart';
    const XML_PATH_REDIRECT = 'bss_multiwishlist/general/redirect';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var WishlistLabel
     */
    protected $wishlistLabel;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Wishlist
     */
    protected $coreWishlist;

    /**
     * Data constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param WishlistLabel $wishlistLabel
     * @param CollectionFactory $collectionFactory
     * @param Wishlist $coreWishlist
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        WishlistLabel $wishlistLabel,
        CollectionFactory $collectionFactory,
        Wishlist $coreWishlist
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->wishlistLabel = $wishlistLabel;
        $this->coreWishlist = $coreWishlist;
        $this->collectionFactory = $collectionFactory;
        $this->_request = $context->getRequest();
    }

    /**
     * @return string
     */
    public function isEnable()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function isRedirect()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_REDIRECT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        $ObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $context = $ObjectManager->get('Magento\Framework\App\Http\Context');
        $isLoggedIn = $context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }

    /**
     * @return \Bss\MultiWishlist\Model\ResourceModel\WishlistLabel\Collection
     */
    public function getWishlistLabels()
    {
        $customer = $this->customerSession->getCustomer();
        $collection = $this->collectionFactory->create();
        $collection = $collection->addFieldToFilter('customer_id', $customer->getId());
        return $collection;
    }

    /**
     * @return array
     */
    public function getLabelIds()
    {

        $wishlist = $this->getWishlistLabels();
        $multiWishlist = [];
        $multiWishlist[0] = 0;
        foreach ($wishlist as $item) {

            if (!in_array($item->getId(), $multiWishlist)) {
                $multiWishlist[] = $item->getId();
            }
        }
        return $multiWishlist;
    }

    /**
     * @param int $id
     * @return int
     */
    public function getWishlistItemsCollection($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $wishlistModel = $objectManager->create('Magento\Wishlist\Model\Wishlist');
        $customer = $this->customerSession->getCustomer();
        if ($customer->getId()) {
            $wishlist = $wishlistModel->loadByCustomerId($customer->getId(), true);
            return $wishlist->getItemCollection()->addFieldToFilter('multi_wishlist_id', $id);
        }
        return 0;
    }

    /**
     * @return \Bss\MultiWishlist\Model\ResourceModel\WishlistLabel\Collection
     */
    public function getWishlistCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @param int $id
     * @return \Magento\Framework\Phrase|string
     */
    public function getWishlistName($id)
    {
        if ($id == 0 ) {
            return __('Main');
        }
        return $this->wishlistLabel->load($id)->getWishlistName();
    }

    /**
     * @return string
     */
    public function removeItemAfterAddCart()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_REMOVE_ITEM_ADDCART,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $param
     * @return string
     */
    public function getParamUrl($param)
    {
        return $this->_request->getParam($param);
    }
}
