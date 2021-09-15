<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\Controller\ResultFactory;

class InstockAjaxGrid extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $resultLayout = $this->_resultLayoutFactory->create();

        $block = $resultLayout->getLayout()->getBlock('preparation.grid.instock');
        $block->setUseAjax(true);
        return $resultLayout;
    }
}
