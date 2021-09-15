<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class EditItem extends AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/EditItem.phtml';

    protected $_catalogProduct;

    public function getInProgressItem()
    {
        return $this->_coreRegistry->registry('current_inprogress_item');
    }

    public function getProduct()
    {
        if (!$this->_catalogProduct)
        {
            $productId = $this->getInProgressItem()->getOrderItem()->getproduct_id();
            $this->_catalogProduct = $this->_productFactory->create()->load($productId);
        }
        return $this->_catalogProduct;
    }

    public function getOptionsAsText()
    {
        $txt = [];

        $options = $this->getInProgressItem()->getOrderItem()->getProductOptions();
        if (isset($options['options']) && count($options['options']) > 0)
        {
            foreach($options['options'] as $option)
                $txt[] = '<b>'.$option['label'].'</b> : '.$option['print_value'];
        }

        return implode('<br>', $txt);
    }

    public function getImageUrl()
    {
        return $this->_product->create()->getImageUrl($this->getProduct()->getId());
    }

    public function getCurrentQty()
    {
        return $this->getInProgressItem()->getipi_qty();
    }

}