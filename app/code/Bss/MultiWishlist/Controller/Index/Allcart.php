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

use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\ItemCarrier;
use Magento\Framework\Controller\ResultFactory;

class Allcart extends \Magento\Wishlist\Controller\Index\Allcart
{

    /**
     * Add all items from wishlist to shopping cart
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $resultForward->forward('noroute');
            return $resultForward;
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            $resultForward->forward('noroute');
            return $resultForward;
        }
        $mwlId = $this->getRequest()->getParam('mwlId');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Bss\MultiWishlist\Helper\Data');
        if ($helper->isEnable()) {
            $redirectUrl = $this->itemCarrier->moveAllToCartExtend($wishlist, $this->getRequest()->getParam('qty'),
                $mwlId);
        } else {
            $redirectUrl = $this->itemCarrier->moveAllToCart($wishlist, $this->getRequest()->getParam('qty'));
        }
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}
