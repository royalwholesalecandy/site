<?php

namespace BoostMyShop\Erp\Controller\Adminhtml\Products;

class Ajax extends \BoostMyShop\Erp\Controller\Adminhtml\Products
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $resultLayout = $this->_resultLayoutFactory->create();

        $block = $resultLayout->getLayout()->getBlock('grid');
        $block->setUseAjax(true);
        return $resultLayout;
    }
}
