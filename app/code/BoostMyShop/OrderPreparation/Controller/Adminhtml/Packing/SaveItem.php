<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class SaveItem extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $data = $this->getRequest()->getPost();

        $result = [];

        try
        {
            $inProgressItem = $this->_inProgressItemFactory->create()->load($data['item_id']);
            $orderItem = $inProgressItem->getOrderItem();

            if (isset($data['new_qty']) && $data['new_qty']) {
                $this->_orderEditor->changeOrderItemQty($inProgressItem->getOrderItem(), $data['new_qty'], $inProgressItem);
            }

            if (isset($data['new_sku']) && $data['new_sku'])
            {
                $productId = $this->_productFactory->create()->getIdBySku($data['new_sku']);
                if (!$productId)
                    throw new \Exception('Unable to find product with sku '.$data['new_sku']);
                $product = $this->_productFactory->create()->load($productId);

                $options = (isset($data['options']) ? $data['options'] : null);
                $this->_orderEditor->changeOrderItemSku($inProgressItem->getOrderItem(), $product, $inProgressItem, $options);
            }

            $this->_orderEditor->updateOrderTotals($orderItem->getorder_id());

            //return product information
            $orderItem = $inProgressItem->getOrderItem();
            $product = $this->_productFactory->create()->load($orderItem->getproduct_id());
            $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();

            $result['success'] = true;
            $result['message'] = __('Changes applied');
            $result['in_progress_item'] = $inProgressItem->getData();
            $result['in_progress_item']['product']['sku'] = $product->getSku();
            $result['in_progress_item']['product']['name'] = $product->getName();
            $result['in_progress_item']['product']['options'] = $this->getOptionsAsHtml($orderItem);
            $result['in_progress_item']['product']['image'] = $this->_productHelper->getImageUrl($product->getId());
            $result['in_progress_item']['product']['location'] = $this->_productHelper->getLocation($product->getId(), $warehouseId);
        }
        catch(\Exception $ex)
        {
            $result['success'] = false;
            $result['message'] = $ex->getMessage();
            $result['stack'] = $ex->getTraceAsString();
        }

        die(json_encode($result));
    }

    protected function getOptionsAsHtml($orderItem)
    {
        $txt = [];
        $options = $orderItem->getProductOptions();

        if (isset($options['options']) && count($options['options']) > 0)
        {
            foreach($options['options'] as $option)
            {
                $txt[] = '<b>'.$option['label'].'</b> : '.$option['print_value'];
            }
        }

        return implode('<br>', $txt);
    }
}
