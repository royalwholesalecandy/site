<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Customer Search Grid Container
 */
namespace MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab\Form;

class CustomerSearch extends \Magento\Backend\Block\Widget
{
    protected function _prepareLayout()
    {
        $this->setChild(
            'search-grid',
            $this->getLayout()->createBlock(
                'MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab\Form\CustomerSearch\Grid',
                'mageworx.customerprices.customersearch.grid'
            )
        );
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('search-grid');
    }
}
