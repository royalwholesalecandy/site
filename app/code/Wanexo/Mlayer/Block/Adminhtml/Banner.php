<?php
namespace Wanexo\Mlayer\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Banner extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_order';
        $this->_blockGroup = 'Wanexo_Mlayer';
        $this->_headerText = __('Banners');
        $this->_addButtonLabel = __('Create New Banner');
        parent::_construct();
    }
}
