<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class ShippingMethodPopup extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $id = $this->getRequest()->getParam('order_id');

        $model = $this->_inProgressFactory->create()->load($id);
        $this->_coreRegistry->register('current_inprogress', $model);

        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Change shipping method'));
        $this->_view->renderLayout();
    }
}
