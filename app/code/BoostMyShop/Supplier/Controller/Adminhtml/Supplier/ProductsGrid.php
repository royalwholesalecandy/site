<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Supplier;

class ProductsGrid extends \BoostMyShop\Supplier\Controller\Adminhtml\Supplier
{
    /**
     * @return void
     */
    public function execute()
    {
        $supId = $this->getRequest()->getParam('sup_id');
        $model = $this->_supplierFactory->create();
        $model->load($supId);

        $this->_coreRegistry->register('current_supplier', $model);

        $resultLayout = $this->_resultLayoutFactory->create();

        return $resultLayout;

    }
}
