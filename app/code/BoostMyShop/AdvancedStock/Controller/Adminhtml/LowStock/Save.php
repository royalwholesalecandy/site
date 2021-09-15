<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\LowStock;

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

                if (preg_match('/([0-9]*):([^=]*)=(.*)/', $line, $matches))
                {
                    list($dummy, $warehouseItemId, $field, $value) = $matches;
                    $data[$warehouseItemId][$field] = $value;
                }
            }

            //apply changes
            foreach($data as $warehouseItemId => $fields)
            {
                $this->saveData($warehouseItemId, $fields);
            }

            $this->messageManager->addSuccess(__('Changes saved.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('An error occured : %1.', $ex->getTraceAsString()));
        }

        $this->_redirect('*/*/index');
    }

    protected function saveData($warehouseItemId, $fields)
    {

        $item = $this->_warehouseItemFactory->create()->load($warehouseItemId);

        foreach($fields as $k => $v)
        {
            if ($k != 'wi_physical_quantity')
                $item->setData($k, $v);
        }
        $item->save();

    }
}
