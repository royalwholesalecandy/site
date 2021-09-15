<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Amasty\Perm\Model\DealerOrderFactory;
use Amasty\Perm\Helper\Data as PermHelper;

class OrderService
{
    protected $_dealerOrderFactory;
    protected $_permHelper;

    public function __construct(
        DealerOrderFactory $dealerOrderFactory,
        PermHelper $permHelper
    ){
        $this->_dealerOrderFactory = $dealerOrderFactory;
        $this->_permHelper = $permHelper;
    }

    public function beforePlace(
        \Magento\Sales\Model\Service\OrderService $orderService,
        OrderInterface $order
    ){
        if ($this->_permHelper->isBackendDealer()) { //from admin area by dealer
            $this->_permHelper->checkPermissionsByCustomerId($order->getCustomerId());
        }

        return [$order];
    }

    public function afterPlace(
        \Magento\Sales\Model\Service\OrderService $orderService,
        OrderInterface $order
    ){
        $this->_permHelper->loadDealers($order);
        if ($this->_permHelper->hasDealers()){
            foreach($this->_permHelper->getDealers() as $dealer){
                $this->_assignToDealer($order, $dealer);
            }
        }

        return $order;
    }

    protected function _assignToDealer($order, \Amasty\Perm\Model\Dealer $dealer)
    {
        $dealerOrder = $this->_dealerOrderFactory->create()
            ->addData([
                'dealer_id' => $dealer->getId(),
                'contactname' => $dealer->getContactname(),
                'order_id' => $order->getId(),
            ])
            ->save();

        return $dealerOrder;
    }
}