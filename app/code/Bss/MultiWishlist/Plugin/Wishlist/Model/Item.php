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
namespace Bss\MultiWishlist\Plugin\Wishlist\Model;

class Item
{
    /**
     * @var \Bss\MultiWishlist\Helper\Data
     */
    protected $helper;

    /**
     * Item constructor.
     * @param \Bss\MultiWishlist\Helper\Data $helper
     */
    public function __construct(
        \Bss\MultiWishlist\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Wishlist\Model\Item $item
     * @param $cart
     * @param bool $delete
     * @return array
     */
    public function beforeAddToCart(\Magento\Wishlist\Model\Item $item, $cart, $delete = false)
    {
        if ($this->helper->isEnable()) {
            $delete = $this->helper->removeItemAfterAddCart();
            return [$cart, $delete];
        }
    }
}
