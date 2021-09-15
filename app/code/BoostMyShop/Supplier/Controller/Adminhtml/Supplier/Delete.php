<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Supplier;

class Delete extends \BoostMyShop\Supplier\Controller\Adminhtml\Supplier
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($supId = $this->getRequest()->getParam('sup_id')) {
            try {
                $model = $this->_supplierFactory->create();
                $model->setId($supId);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the supplier.'));
                $this->_redirect('*/*/index');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('adminhtml/*/edit', ['sup_id' => $supId]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find the supplier to delete.'));
        $this->_redirect('*/*/index');
    }
}
