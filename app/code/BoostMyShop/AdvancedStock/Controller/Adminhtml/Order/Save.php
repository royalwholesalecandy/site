<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Order;

class Save extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Order
{

    /**
     * @return void
     */
    public function execute()
    {

        $datas = $this->getRequest()->getPost('advancedstock');
        $orderId = $this->getRequest()->getPost('order_id');

        try
        {
            foreach($datas as $itemId => $itemData)
            {
                $item = $this->_extendedItemFactory->create()->load($itemId);
                foreach($itemData as $k => $v)
                    $item->setData($k, $v);
                $item->save();
            }

            $this->messageManager->addSuccess(__('Changes successfully saved'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('An error occured : %1', $ex->getMessage()));
        }

        $this->_redirect('sales/order/view', ['order_id' => $orderId, 'active_tab' => 'order_advancedstock']);
    }

}
