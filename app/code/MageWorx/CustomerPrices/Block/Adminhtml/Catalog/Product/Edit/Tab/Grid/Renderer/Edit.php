<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab\Grid\Renderer;

/**
 * Adminhtml CustomerPrices Grid Edit Renderer
 */
class Edit extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $result = '<a href="#" onclick="editProductCustomerPrice('.$row->getData('customer_id')
                  .',\''.trim($row->getData('email')).'\',\''.$row->getData('price').'\',\''
                  .$row->getData('special_price')
                  .'\'); return false;" style="cursor:pointer;">'.__('Edit').'</a>';
        return $result;
    }
}
