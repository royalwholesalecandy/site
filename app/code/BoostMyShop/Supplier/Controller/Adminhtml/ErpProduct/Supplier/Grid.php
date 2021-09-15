<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\ErpProduct\Supplier;

class Grid extends \BoostMyShop\Supplier\Controller\Adminhtml\ErpProduct
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $resultLayout = $this->_resultLayoutFactory->create();

        $block = $resultLayout->getLayout()->getBlock('supplier');
        $block->setUseAjax(true);
        return $resultLayout;

    }
}
