<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab\Grid\Renderer;

/**
 * Adminhtml CustomerPrices Grid Customer Id Renderer
 */
class CustomerId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $result = '<a href="' . $this->getUrl(
                'customer/index/edit',
                ['id' => $row->getCustomerId()]
            ) . '" style="cursor:pointer;">' . $row->getCustomerId() . '</a>';

        return $result;
    }
}
