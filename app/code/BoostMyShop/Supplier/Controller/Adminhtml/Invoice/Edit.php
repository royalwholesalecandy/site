<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Invoice;

class Edit extends \BoostMyShop\Supplier\Controller\Adminhtml\Invoice
{
    /**
     * @return void
     */
    public function execute()
    {
        
        $id = $this->getRequest()->getParam('bsi_id');

        $model = $this->_invoiceFactory->create();

        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                $this->messageManager->addError(__('This Invoice no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }

        $this->_coreRegistry->register('current_supplier_invoice', $model);

        if ($model->getbsi_total_applied() > $model->getbsi_total())
            $this->messageManager->addError(__('Warning, total applied exceeds invoice total.'));
        if ($model->getbsi_total_applied() < $model->getbsi_total())
            $this->messageManager->addError(__('Warning, this invoice is not totally applied.'));

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Supplier Invoice'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__("Edit Supplier Invoice"));

        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() 
            ? __("Supplier Invoice %1 for %2", $model->getBsiReference(), $model->getSupplier()->getsup_name()) 
            : __('New Supplier Invoice')
        );
        $this->_view->renderLayout();
    }
}
