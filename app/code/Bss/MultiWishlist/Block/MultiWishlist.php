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
use Magento\Framework\View\Element\Template\Context;

class MultiWishlist extends Template
{

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Model
     */
    protected $model;

    /**
     * MultiWishlist constructor.
     * @param Context $context
     * @param Helper $helper
     * @param Model $model
     * @param array $data
     */
    public function __construct(
        Context $context,
        Helper $helper,
        Model $model,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->model = $model;
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
     * @return bool
     */
    public function isRedirect()
    {
        return $this->helper->isRedirect();
    }

    /**
     * @return string
     */
    public function getUrlWishlist()
    {
        return $this->getUrl("wishlist");
    }

    /**
     * @return string
     */
    public function getUrlPopup()
    {
        return $this->getUrl("multiwishlist/index/popup");
    }
}
