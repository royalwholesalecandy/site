<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Plugin;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Amasty\Perm\Helper\Data as PermHelper;

class OrderEmailSenderOrderSender{

    protected $_permHelper;

    public function __construct(
        PermHelper $permHelper
    ){
        $this->_permHelper = $permHelper;
    }

    public function beforeSend(
        OrderSender $orderSender,
        Order $order,
        $forceSyncMode = false
    ){
        $this->_permHelper->loadDealers($order);
    }
}