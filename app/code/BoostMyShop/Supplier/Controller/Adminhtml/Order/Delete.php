<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class Delete extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($poId = $this->getRequest()->getParam('po_id')) {
            try {
                $model = $this->_orderFactory->create();
                $model->setId($poId);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the order.'));
                $this->_redirect('*/*/index');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['po_id' => $poId]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find the order to delete.'));
        $this->_redirect('adminhtml/*/');
    }
}
