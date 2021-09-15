<?php

namespace BoostMyShop\Supplier\Block\Order\Edit;

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
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Purchase Order'));
    }

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;

        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    protected function getOrder()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Order\Edit\Tab\Main')->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'misc_section',
            [
                'label' => __('Miscellaneous'),
                'title' => __('Miscellaneous'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Order\Edit\Tab\Misc')->toHtml()
            ]
        );

        if ($this->getOrder()->getId())
        {
            $this->addTab(
                'products_section',
                [
                    'label' => __('Products'),
                    'title' => __('Products'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Order\Edit\Tab\Products')->toHtml()
                ]
            );

            $this->addTab(
                'add_products_section',
                [
                    'label' => __('Add Products'),
                    'title' => __('Add Products'),
                    'url'       => $this->getUrl('*/*/addProductsGrid', array('_current'=>true)),
                    'class'     => 'ajax'
                ]
            );

            $this->addTab(
                'reception_section',
                [
                    'label' => __('Receptions'),
                    'title' => __('Receptions'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Order\Edit\Tab\Reception')->toHtml()
                ]
            );

            $this->addTab(
                'invoice_section',
                [
                    'label' => __('Invoices'),
                    'title' => __('Invoices'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Order\Edit\Tab\Invoices')->toHtml()
                ]
            );

            $this->addTab(
                'import_export',
                [
                    'label' => __('Import / export'),
                    'title' => __('Import / export'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Order\Edit\Tab\ImportExport')->toHtml()
                ]
            );

        }

        //raise event to add tabs
        $this->_eventManager->dispatch('bms_supplier_order_edit_tabs', ['order' => $this->getOrder(), 'tabs' => $this, 'layout' => $this->getLayout()]);

        return parent::_beforeToHtml();
    }
}
