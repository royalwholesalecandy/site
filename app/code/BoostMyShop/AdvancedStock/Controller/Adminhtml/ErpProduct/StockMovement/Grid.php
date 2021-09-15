<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\ErpProduct\StockMovement;

class Grid extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\ErpProduct
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $resultLayout = $this->_resultLayoutFactory->create();

        $block = $resultLayout->getLayout()->getBlock('stockmovement');
        $block->setUseAjax(true);
        return $resultLayout;

    }
}
