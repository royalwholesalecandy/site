<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class EditItem extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $id = $this->getRequest()->getParam('item_id');
        if (!$id)
            $id = $this->getRequest()->getPost('item_id');
        $model = $this->_inProgressItemFactory->create()->load($id);
        $this->_coreRegistry->register('current_inprogress_item', $model);

        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Edit Order Item'));
        $this->_view->renderLayout();
    }
}
