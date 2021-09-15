<?php
namespace BoostMyShop\OrderPreparation\Block\Preparation;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected $_coreRegistry;

    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('tab_container');
        $this->setTitle(__('Order Preparation'));

    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $tabs = ['instock' => 'In stock', 'outofstock' => 'Backorder', 'holded' => 'Holded'];

        $block =  $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Preparation\Tab\InStock');
        $this->addTab(
            'tab_instock',
            [
                'label' => __('In Stock'),
                'title' => __('In Stock'),
                'content' => $block->toHtml()
            ]
        );

        $block =  $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Preparation\Tab\BackOrder');
        $this->addTab(
            'tab_backorder',
            [
                'label' => __('Backorder'),
                'title' => __('Backorder'),
                'content' => $block->toHtml()
            ]
        );

        $block =  $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Preparation\Tab\Holded');
        $this->addTab(
            'tab_holded',
            [
                'label' => __('On Hold'),
                'title' => __('On Hold'),
                'content' => $block->toHtml()
            ]
        );

        $block =  $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Preparation\InProgress');
        $this->addTab(
            'tab_progress',
            [
                'label' => __('In progress'),
                'title' => __('In progress'),
                'content' => $block->toHtml(),
                'active' => true
            ]
        );

        return parent::_beforeToHtml();
    }
}