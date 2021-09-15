<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\CarrierTemplate;

class Delete extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\CarrierTemplate
{
    /**
     * @return void
     */
    public function execute()
    {
        $ctId = $this->getRequest()->getParam('ct_id');
        $model = $this->_carrierTemplateFactory->create()->load($ctId);
        $model->delete();

        $this->messageManager->addSuccess(__('Carrier template deleted.'));
        $this->_redirect('*/*/index');
    }
}
