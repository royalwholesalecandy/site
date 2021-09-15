<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\Controller\ResultFactory;

class BackorderAjaxGrid extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $resultLayout = $this->_resultLayoutFactory->create();

        $block = $resultLayout->getLayout()->getBlock('preparation.grid.backorder');
        $block->setUseAjax(true);
        return $resultLayout;
    }
}
