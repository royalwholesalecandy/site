<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\PriceRule\Edit;

/**
 * PriceRule page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Amazon Product Price Rule'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('Product Price Rule'),
                'title' => __('Product Price Rule'),
                'content' => $this->getLayout()->createBlock(
                    'Webkul\AmazonMagentoConnect\Block\Adminhtml\PriceRule\Edit\Tab\Main'
                )->toHtml(),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
