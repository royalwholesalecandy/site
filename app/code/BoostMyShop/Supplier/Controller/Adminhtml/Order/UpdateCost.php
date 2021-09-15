<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class UpdateCost extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    public function execute()
    {
        $poId = (int)$this->getRequest()->getParam('po_id');

        try
        {
            $order = $this->_orderFactory->create()->load($poId);
            $order->updateProductCosts();
            $this->messageManager->addSuccess(__('Costs updated.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__($ex->getMessage()));
        }

        $this->_redirect('*/*/Edit', ['po_id' => $order->getId()]);
    }

}
