<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\Controller\ResultFactory;

class HoldedAjaxGrid extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $resultLayout = $this->_resultLayoutFactory->create();

        $block = $resultLayout->getLayout()->getBlock('preparation.grid.holded');
        $block->setUseAjax(true);
        return $resultLayout;
    }
}
