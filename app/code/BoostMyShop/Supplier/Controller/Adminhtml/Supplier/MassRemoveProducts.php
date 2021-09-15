<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Supplier;

class MassRemoveProducts extends \BoostMyShop\Supplier\Controller\Adminhtml\Supplier
{
    public function execute()
    {
        $supId = (int)$this->getRequest()->getParam('sup_id');
        $data = $this->getRequest()->getPostValue();
        $model = $this->_supplierFactory->create()->load($supId);

        $productIds = $data['massaction'];
        foreach($productIds as $productId)
        {
            $model->removeProduct($productId);
        }

        $this->messageManager->addSuccess(__('Products associated to supplier.'));
        $this->_redirect('supplier/supplier/edit', ['sup_id' => $supId, 'active_tab' => 'products_section']);

    }

}
