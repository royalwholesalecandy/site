<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class Notify extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    public function execute()
    {
        $poId = (int)$this->getRequest()->getParam('po_id');

        try
        {
            $order = $this->_orderFactory->create()->load($poId);
            $this->_notification->notifyToSupplier($order);
            $this->messageManager->addSuccess(__('Supplier notified.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__($ex->getMessage()));
        }

        $this->_redirect('*/*/Edit', ['po_id' => $order->getId()]);
    }

}
