<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse;

class Delete extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($stockId = $this->getRequest()->getParam('w_id')) {
            try {

                if ($stockId == 1)
                    throw new \Exception('You can not delete the default warehouse');

                $model = $this->_warehouseFactory->create();
                $model->setId($stockId);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the warehouse.'));
                $this->_redirect('*/*/index');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['w_id' => $stockId]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find the warehouse to delete.'));
        $this->_redirect('*/*/index');
    }
}
