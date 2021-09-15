<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderLoadAfterObserver implements ObserverInterface
{
    public function __construct(
        \Amasty\Perm\Helper\Data $permHelper
    ){
        $this->_permHelper = $permHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getData('data_object');
        $this->_permHelper->checkPermissionsByOrder($order);
    }
}
