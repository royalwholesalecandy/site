<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit;

class Tab extends \Magento\Backend\Block\Text\ListText implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Prices per Customer');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Prices per Customer');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return $this->_isEditing();
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return !$this->_isEditing();
    }

    /**
     * Check whether we edit existing rule or adding new one
     *
     * @return bool
     */
    protected function _isEditing()
    {
        return true;
    }
}
