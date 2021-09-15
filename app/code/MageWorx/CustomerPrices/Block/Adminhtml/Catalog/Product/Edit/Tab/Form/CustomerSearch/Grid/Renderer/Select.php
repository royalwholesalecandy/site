<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab\Form\CustomerSearch\Grid\Renderer;

/**
 * Adminhtml CustomerPrices Customer Search Grid Select Renderer
 */
class Select extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $result = '<a href="" onclick="selectAddCustomer(\''.trim($row->getData('email')).'\','
                   .$row->getData('entity_id').'); return false;" style="cursor:pointer;">'.__('Select').'</a>';
        return $result;
    }
}
