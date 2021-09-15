<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Transit;

class Grid extends \BoostMyShop\Supplier\Controller\Adminhtml\Transit
{
    /**
     * @return void
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('supplier.transit.grid.container');
        $block->setUseAjax(true);
        return $resultLayout;

    }
}
