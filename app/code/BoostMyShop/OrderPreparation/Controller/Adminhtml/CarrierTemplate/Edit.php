<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\CarrierTemplate;

class Edit extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\CarrierTemplate
{
    /**
     * @return void
     */
    public function execute()
    {

        $ctId = $this->getRequest()->getParam('ct_id');

        $model = $this->_carrierTemplateFactory->create();

        if ($ctId) {
            $model->load($ctId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This template no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }


        $this->_coreRegistry->register('current_carrier_template', $model);

        if (isset($ctId)) {
            $breadcrumb = __('Edit template');
        } else {
            $breadcrumb = __('New template');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipping Label Template'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() ? __("Edit Template '%1'", $model->getct_name()) : __('New Template'));
        $this->_view->renderLayout();
    }
}
