<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class Products extends AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/Products.phtml';

    public function getProducts()
    {
        return $this->currentOrderInProgress()->getAllItems();
    }

    public function getProductLocation($productId)
    {
        return $this->_product->create()->getLocation($productId, $this->_preparationRegistry->getCurrentWarehouseId());
    }

    public function getProductImageUrl($productId)
    {
        return $this->_product->create()->getImageUrl($productId);
    }

    public function getBarcode($productId)
    {
        return $this->_product->create()->getBarcode($productId);
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/confirmPacking');
    }

    public function getEditOrderItemUrl($item)
    {
        return $this->getUrl('*/*/editItem', ['item_id' => $item->getId()]);
    }

    public function isOrderEditorEnabled()
    {
        return $this->_config->isOrderEditorEnabled();
    }

    public function getConfigurableOptionsAsText($item)
    {
        $txt = array();

        if ($item->getOrderItem()->getparent_item_id()) {
            $parentItem = $this->_orderItemFactory->create()->load($item->getOrderItem()->getparent_item_id());
            $options = $parentItem->getProductOptions();
            if (isset($options['attributes_info']) && is_array($options['attributes_info']))
            {
                foreach($options['attributes_info'] as $info)
                {
                    $txt[] = $info['label'].': '.$info['value'];
                }
            }
        }

        return implode('<br>', $txt);
    }

    public function getProductOptions($item)
    {
        $txt = [];
        $options = $item->getOrderItem()->getProductOptions();


        if (isset($options['options']) && count($options['options']) > 0)
        {
            foreach($options['options'] as $option)
            {
                $txt[] = '<b>'.$option['label'].'</b> : '.$option['print_value'];
            }
        }
        else
        {
            //try with parent
            if ($item->getOrderItem()->getparent_item_id())
            {
                $parentItem = $this->_orderItemFactory->create()->load($item->getOrderItem()->getparent_item_id());
                $options = $parentItem->getProductOptions();
                if (isset($options['options']) && count($options['options']) > 0)
                {
                    foreach($options['options'] as $option)
                    {
                        $txt[] = $option['label'].' : '.$option['print_value'];
                    }
                }
            }
            else
                return false;
        }

        return implode('<br>', $txt);
    }

}