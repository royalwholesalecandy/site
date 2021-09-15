<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Invoice;

class Delete extends \BoostMyShop\Supplier\Controller\Adminhtml\Invoice
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($bsiId = $this->getRequest()->getParam('bsi_id')) {
            try {
                $model = $this->_invoiceFactory->create();
                $model->setId($bsiId);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the invoice.'));
                $this->_redirect('*/*/index');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('adminhtml/*/edit', ['bsi_id' => $bsiId]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find the invoice to delete.'));
        $this->_redirect('*/*/index');
    }
}
