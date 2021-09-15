<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class Receive extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    /**
     * @return void
     */
    public function execute()
    {

        $poId = $this->getRequest()->getParam('po_id');
        $model = $this->_orderFactory->create();
        $model->load($poId);
        if (!$model->getId()) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }

        $this->_coreRegistry->register('current_purchase_order', $model);

        if (!$this->_config->getBarcodeAttribute())
            $this->messageManager->addError(__('Barcode reception is disabled because the barcode attribute is not configured.'));


        $this->_initAction();


        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Receive Purchase Order %1', $model->getPoReference()));

        $this->_view->renderLayout();
    }
}
