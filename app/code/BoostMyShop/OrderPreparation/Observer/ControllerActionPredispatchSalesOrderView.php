<?php

namespace BoostMyShop\OrderPreparation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ControllerActionPredispatchSalesOrderView implements ObserverInterface
{
    protected $_context;
    protected $_state;
    protected $_inProgressCollectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\State $state,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressCollectionFactory
    ) {
        $this->_context = $context;
        $this->_state = $state;
        $this->_inProgressCollectionFactory = $inProgressCollectionFactory;
    }

    public function execute(EventObserver $observer)
    {
        if ($this->_state->getAreaCode() == 'adminhtml')
        {
            $orderId = $this->_context->getRequest()->getParam('order_id');
            $collection = $this->_inProgressCollectionFactory->create()->addOrderFilter($orderId);
            foreach($collection as $inProgresItem)
                $this->_context->getMessageManager()->addError(__('This order is being prepared by %1', $inProgresItem->getOperatorName()));
        }

        return $this;
    }

}
