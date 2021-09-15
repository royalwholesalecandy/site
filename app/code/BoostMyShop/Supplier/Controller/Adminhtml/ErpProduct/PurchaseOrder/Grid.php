<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\ErpProduct\PurchaseOrder;

class Grid extends \BoostMyShop\Supplier\Controller\Adminhtml\ErpProduct
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $resultLayout = $this->_resultLayoutFactory->create();

        $block = $resultLayout->getLayout()->getBlock('purchaseorder');
        $block->setUseAjax(true);
        return $resultLayout;

    }
}
