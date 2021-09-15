<?php
namespace Wanexo\Brand\Block\Adminhtml\Brand\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;


class Tabs extends WidgetTabs
{
    
    protected function _construct()
    {
        parent::_construct();
        $this->setId('brand_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Brand Information'));
    }
}
