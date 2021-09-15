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
namespace Bss\MultiWishlist\Block;

use Bss\MultiWishlist\Helper\Data as Helper;
use Magento\Framework\View\Element\Template;
use Bss\MultiWishlist\Model\WishlistLabel as Model;
use Magento\Customer\Model\Session;


class Popup extends Template
{

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Model
     */
    protected $wishlistlabel;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Popup constructor.
     * @param Template\Context $context
     * @param Helper $helper
     * @param Model $wishlistlabel
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Helper $helper,
        Model $wishlistlabel,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->wishlistlabel = $wishlistlabel;
        $this->customerSession = $customerSession;
    }

    /**
     * @return Helper
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return \Bss\MultiWishlist\Model\ResourceModel\WishlistLabel\Collection
     */
    public function getMyWishlist()
    {
        return $this->helper->getWishlistLabels();
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->helper->isCustomerLoggedIn();
    }

    /**
     * @return string
     */
    public function getActionWl()
    {
        return $this->getData('action');
    }

    /**
     * @return mixed
     */
    public function getUnwishlist()
    {
        return $this->getData('unwishlist');
    }

    /**
     * @return string
     */
    public function getUrlAction()
    {
        if ($this->getActionWl() == 'add') {
            return $this->getUrl("multiwishlist/index/assignWishlist/");
        }
        if ($this->getActionWl() == 'copy') {
            return $this->getUrl("multiwishlist/index/copy");
        }
        if ($this->getActionWl() == 'move') {
            return $this->getUrl("multiwishlist/index/movetowishlist");
        }
        if ($this->getActionWl() == 'movefromcart') {
            return $this->getUrl("multiwishlist/index/assignWishlistFromCart");
        }
        return '';
    }

    /**
     * @return string
     */
    public function getUrlCreateWishList()
    {
        return $this->getUrl("multiwishlist/index/create/ajax/1");
    }
}
