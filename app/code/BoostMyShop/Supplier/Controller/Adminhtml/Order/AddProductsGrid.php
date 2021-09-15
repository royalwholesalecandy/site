<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class AddProductsGrid extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    /**
     * @return void
     */
    public function execute()
    {
        $poId = $this->getRequest()->getParam('po_id');
        $model = $this->_orderFactory->create();
        $model->load($poId);

        $this->_coreRegistry->register('current_purchase_order', $model);

        $resultLayout = $this->_resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('supplier.order.addproducts');
        $block->setUseAjax(true);
        return $resultLayout;

    }
}
