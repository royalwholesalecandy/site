<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class SubstitutionGrid extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {

        $resultLayout = $this->_resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('orderpreparation.packing.substitutiongrid');
        $block->setUseAjax(true);
        return $resultLayout;
    }
}
