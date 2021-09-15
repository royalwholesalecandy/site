<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected $_coreRegistry;

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
        $this->setTitle(__('Supplier Information'));
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

    protected function getSupplier()
    {
        return $this->_coreRegistry->registry('current_supplier');
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
                'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Main')->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'order_settings_section',
            [
                'label' => __('Settings'),
                'title' => __('Settings'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Supplier\Edit\Tab\PoSettings')->toHtml()
            ]
        );

        $this->addTab(
            'contacts_section',
            [
                'label' => __('Contacts'),
                'title' => __('Contacts'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Contacts')->toHtml()
            ]
        );

        if ($this->getSupplier()->getId())
        {
            $this->addTab(
                'notification',
                [
                    'label' => __('Notifications'),
                    'title' => __('Notifications'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Notification')->toHtml()
                ]
            );

            $this->addTab(
                'products_section',
                [
                    'label' => __('Products'),
                    'title' => __('Products'),
                    'url'       => $this->getUrl('*/*/ProductsGrid', array('_current'=>true, 'sup_id' => $this->getSupplier()->getId())),
                    'class'     => 'ajax'
                ]
            );

            $this->addTab(
                'po_section',
                [
                    'label' => __('Orders'),
                    'title' => __('Orders'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Po')->toHtml()
                ]
            );
        }

        $this->_eventManager->dispatch('bms_supplier_edit_tabs', ['supplier' => $this->getSupplier(), 'tabs' => $this, 'layout' => $this->getLayout()]);

        return parent::_beforeToHtml();
    }
}
