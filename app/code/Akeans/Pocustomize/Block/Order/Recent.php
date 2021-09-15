<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Akeans\Pocustomize\Block\Order;

use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class Recent extends \Magento\Sales\Block\Order\Recent
{
    

    /**
     * Get recently placed orders. By default they will be limited by 5.
     */
    protected function getRecentOrders()
    {
        $orders = $this->_orderCollectionFactory->create()->addAttributeToSelect(
            '*'
        )->addAttributeToFilter(
            'customer_id',
            $this->_customerSession->getCustomerId()
        )->addAttributeToFilter(
            'status',
            ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
        )->addAttributeToSort(
            'created_at',
            'desc'
        )->setPageSize(
            self::ORDER_LIMIT
        )->load();
        $this->setOrders($orders);
    }

   
}
