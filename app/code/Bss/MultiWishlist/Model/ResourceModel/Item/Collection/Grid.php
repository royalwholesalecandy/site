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
namespace Bss\MultiWishlist\Model\ResourceModel\Item\Collection;

class Grid extends \Magento\Wishlist\Model\ResourceModel\Item\Collection\Grid
{
    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Bss\MultiWishlist\Helper\Data');
        if ($helper->isEnable()) {
            $this->getSelect()->joinLeft(
                    ['ct' => $this->getTable('bss_multiwishlist')],
                    'main_table.multi_wishlist_id = ct.multi_wishlist_id',
                    '*'
                );
        }
        return $this;
    }
}
