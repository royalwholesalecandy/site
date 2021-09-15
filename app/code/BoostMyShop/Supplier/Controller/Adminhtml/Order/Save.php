<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class Save extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    protected function _filterPostData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            ['po_eta' => $this->_dateFilter, 'po_payment_date' => $this->_dateFilter, 'po_invoice_date' => $this->_dateFilter],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        return $data;
    }

    public function execute()
    {

        $poId = (int)$this->getRequest()->getParam('po_id');
        $currentTab = str_replace('page_tabs_', '', $this->getRequest()->getParam('current_tab'));
        $data = $this->getRequest()->getPostValue();
        
        if (!$data) {
            $this->_redirect('adminhtml/*/');
            return;
        }
        /** @var $model \Magento\User\Model\User */
        $model = $this->_orderFactory->create()->load($poId);
        if ($poId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_redirect('supplier/order/index');
            return;
        }

        try {

            $data = $this->_filterPostData($data);
            $data = array_merge($model->getData(), $data);
            $model->setData($data);
            $model->save();

            foreach($model->getAllItems() as $item)
            {
                if (isset($data['products'][$item->getId()]))
                    $this->updateOrderProduct($item, $data['products'][$item->getId()]);
            }

            if ($this->_config->getSetting('general/pack_quantity')){
                if (isset($data['pack_qty']))
                    $this->addProducts($model, $data['pack_qty'],'pack_qty');
            } else {
                if (isset($data['products_to_add']))
                    $this->addProducts($model, $data['products_to_add'],'products_to_add');
            }

            $delimiter = isset($data['delimiter']) ? $data['delimiter'] : ';';
            $this->processImport($model, $poId, $delimiter);

            $model->updateDeliveryProgress();   //safe & quick
            $model->updateTotals();             //safe & quick

            //todo : perform these actions only if really needed !
            $model->updateQtyToReceive();
            $model->updateExtendedCosts();
            $model->updateMissingPrices();

            $this->messageManager->addSuccess(__('You saved the order.'));
            $this->_redirect('*/*/Edit', ['po_id' => $model->getId(), 'active_tab' => $currentTab]);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Validator\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->redirectToEdit($model, $data);
        }
    }

    /**
     * Update single order product from post data
     *
     * @param $orderProduct
     * @param $data
     */
    protected function updateOrderProduct($orderProduct, $data)
    {

        if (isset($data['remove']))
            $orderProduct->delete();
        else
        {
                $orderProduct->setPopQty($data['qty']);
                $orderProduct->setPopPrice($data['price']);
                if (isset($data['discount']))
                    $orderProduct->setPopDiscountPercent($data['discount']);
                $orderProduct->setPopTaxRate($data['tax_rate']);
                $orderProduct->setPopSupplierSku($data['supplier_sku']);
                if (isset($data['eta']))
                    $orderProduct->setPopEta($data['eta']);
                if(array_key_exists('qty_pack', $data))
                    $orderProduct->setPopQtyPack($data['qty_pack']);
                $orderProduct->save();
                $hasChanges = true;
        }

    }

    /**
     * @param $order
     * @param $productsToAdd
     */
    protected function addProducts($order, $productsToAdd, $arg)
    {
        $hasChanges = false;

        if($arg == 'products_to_add'){
            $productsToAdd = explode(';', $productsToAdd);
            foreach($productsToAdd as $item)
            {
                if (count(explode('=', $item)) == 2)
                {
                    list($productId, $qty) = explode('=', $item);
                    if ($qty > 0)
                    {
                        $order->addProduct($productId, $qty);
                        $hasChanges = true;
                    }
                }
            }
        }

        if($arg == 'pack_qty'){
            $additional = array();
            if(count($productsToAdd) > 0)
            {
                foreach ($productsToAdd as $productId => $value) 
                {
                    $qty = $value['qty'];
                    $packQty = $value['qty_pack'];
                    if ($qty > 0)
                    {
                        $additional = array('qty_pack' => $packQty);
                        $order->addProduct($productId, $qty, $additional);
                        $hasChanges = true;
                    }
                }
            }
        }

        return $hasChanges;
    }

    /**
     * @param \Magento\User\Model\User $model
     * @param array $data
     * @return void
     */
    protected function redirectToEdit(\BoostMyShop\Supplier\Model\Order $model, array $data)
    {
        $this->_getSession()->setUserData($data);
        $arguments = $model->getId() ? ['po_id' => $model->getId()] : [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        $this->_redirect('supplier/order/edit', $arguments);
    }

    protected function processImport($model, $poId, $delimiter)
    {
        $importResult = null;
        try
        {
            $importResult = $this->_csvImport->checkPoImport($model, $poId, $delimiter);
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('Error: %1', $ex->getMessage()));
        }

        if(is_array($importResult) && !empty($importResult)){
            $found = array();
            $notFound = array();
            $error = array();
            foreach ($importResult as $key => $value) {
                if(array_key_exists('found', $value))
                    $found[] = $value['found'];

                if(array_key_exists('not_found', $value))
                    $notFound[] = $value['not_found'];

                if(array_key_exists('error', $value))
                    $error[] = $value['error'];
            }

            if(count($error) > 0)
                $this->messageManager->addError(__('sku or qty is missing in %1 row(s)', count($error)));

            if(count($notFound) > 0)
                $this->messageManager->addError(__('Sku "%1" unknown', implode(", ", $notFound)));

            if(count($found) > 0)
                $this->messageManager->addSuccess(__('Csv file has been imported : %1 row(s) processed', count($found)));
        }

    }

}
