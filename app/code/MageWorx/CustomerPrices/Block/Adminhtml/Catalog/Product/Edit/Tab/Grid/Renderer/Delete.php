<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab\Grid\Renderer;

/**
 * Adminhtml CustomerPrices Grid Delete Renderer
 */
class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $result = '<a href="#" onclick="deleteProductCustomerPrice(' . $row->getData('entity_id') . ',\''
            . $this->getUrl('mageworx_customerprices/*/deletecustomerprices') . '\'); 
            return false;" style="cursor:pointer;" 
                  class="action-menu-item">' . __('Delete') . '</a>';

        return $result;
    }
}
