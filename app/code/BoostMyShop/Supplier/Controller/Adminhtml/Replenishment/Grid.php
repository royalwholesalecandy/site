<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Replenishment;

class Grid extends \BoostMyShop\Supplier\Controller\Adminhtml\Replenishment
{
    /**
     * @return void
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('supplier.replenishment.grid.container');
        $block->setUseAjax(true);
        return $resultLayout;

    }
}
