<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class SubmitTracking extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        try
        {
            $tracking = $this->getRequest()->getPost('tracking');
            $this->currentOrderInProgress()->addTracking($tracking);

            $this->messageManager->addSuccess(__('Tracking added to order.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
        }

        $this->_redirect('*/*/Index', ['order_id' => $this->currentOrderInProgress()->getId()]);
    }

    public function currentOrderInProgress()
    {
        return $this->_coreRegistry->registry('current_packing_order');
    }

}
