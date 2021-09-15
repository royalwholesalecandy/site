<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Customer Search Js
 */
namespace MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab\Form\CustomerSearch;

class Js extends \Magento\Backend\Block\Widget
{
     /**
     * @return string
     */
    public function getModalTitle()
    {
        return __('Search Customer');
    }
    
    /**
     * @return string
     */
    public function getCloseButtonText()
    {
        return __('Close');
    }
}
