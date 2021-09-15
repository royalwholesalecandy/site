<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse;

class OrdersGrid extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse
{
    /**
     * @return void
     */
    public function execute()
    {
        $warehouseId = $this->getRequest()->getParam('w_id');
        $model = $this->_warehouseFactory->create()->load($warehouseId);
        $this->_coreRegistry->register('current_warehouse', $model);

        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
