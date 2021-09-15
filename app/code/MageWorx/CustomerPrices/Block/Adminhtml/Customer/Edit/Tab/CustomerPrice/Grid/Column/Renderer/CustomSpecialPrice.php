<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Block\Adminhtml\Customer\Edit\Tab\CustomerPrice\Grid\Column\Renderer;

class CustomSpecialPrice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $colId = 'custom_special_price';

        $html = '<input name="' . $colId . '-' . $row->getId() .'"
                class="input-text ' . $colId . '" value="' . $row->getCustomSpecialPrice()  . '" />';

        return $html;
    }
}
