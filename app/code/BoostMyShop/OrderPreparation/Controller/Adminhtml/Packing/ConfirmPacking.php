<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class ConfirmPacking extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        try
        {
            $quantities = $this->getRequest()->getPost('products');
            $totalWeight = $this->getRequest()->getPost('total_weight');

            $createInvoice = $this->_configFactory->create()->getCreateInvoice();
            $createShipment = $this->_configFactory->create()->getCreateShipment();
            $this->currentOrderInProgress()->pack($createShipment, $createInvoice, $quantities, $totalWeight);

            $this->_eventManager->dispatch('bms_orderpreparation_order_after_pack', ['order_in_progress' => $this->currentOrderInProgress(), 'products' => $quantities]);

            $this->messageManager->addSuccess(__('Order packed.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
            $this->_logger->logException($ex);
        }

        $this->_redirect('*/*/Index', ['order_id' => $this->currentOrderInProgress()->getId(), 'download' => 1      ]);
    }

    public function currentOrderInProgress()
    {
        return $this->_coreRegistry->registry('current_packing_order');
    }

}
