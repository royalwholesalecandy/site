<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\MassStockEditor;

class Save extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\MassStockEditor
{

    /**
     * @return void
     */
    public function execute()
    {
        try
        {
            $changes = explode(';', $this->getRequest()->getParam('changes'));
            $data = [];

            //load changes
            foreach($changes as $line)
            {
                if (!$line)
                    continue;

                if (preg_match('/([0-9]*)_([0-9]*):([^=]*)=(.*)/', $line, $matches))
                {
                    list($dummy, $productId, $warehouseId, $field, $value) = $matches;
                    $data[$productId][$warehouseId][$field] = $value;
                }
            }

            //apply changes
            foreach($data as $productId => $warehouses)
            {
                foreach($warehouses as $warehouseId => $fields)
                {
                    $this->saveData($productId, $warehouseId, $fields);
                }
            }

            $this->messageManager->addSuccess(__('Changes saved.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('An error occured : %1.', $ex->getTraceAsString()));
        }

        $this->_redirect('*/*/index');
    }

    protected function saveData($productId, $warehouseId, $fields)
    {

        $item = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $warehouseId);

        foreach($fields as $k => $v)
        {
            if ($k != 'wi_physical_quantity')
                $item->setData($k, $v);
        }
        $item->save();

        //update quantity with stock movements
        if (isset($fields['wi_physical_quantity']))
        {
            if ($fields['wi_physical_quantity'] != $item->getwi_physical_quantity())
            {
                $userId = null;
                if ($this->_backendAuthSession->getUser())
                    $userId = $this->_backendAuthSession->getUser()->getId();
                $this->_stockMovementFactory->create()->updateProductQuantity($productId,
                                                                                $warehouseId,
                                                                                $item->getwi_physical_quantity(),
                                                                                $fields['wi_physical_quantity'],
                                                                                'From mass stock editor',
                                                                                $userId);
            }
        }
    }
}
