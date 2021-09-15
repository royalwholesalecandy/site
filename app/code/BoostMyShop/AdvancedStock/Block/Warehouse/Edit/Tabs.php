<?php

namespace BoostMyShop\AdvancedStock\Block\Warehouse\Edit;

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
        $this->setTitle(__('Warehouse Information'));
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

    protected function getWarehouse()
    {
        return $this->_coreRegistry->registry('current_warehouse');
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
                'content' => $this->getLayout()->createBlock('BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab\Main')->toHtml(),
                'active' => true
            ]
        );

        if ($this->getWarehouse()->getId()) {
            $this->addTab(
                'figures',
                [
                    'label' => __('Figures'),
                    'title' => __('Figures'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab\Figures')->toHtml()
                ]
            );
            
            $this->addTab(
                'products_section',
                [
                    'label' => __('Products'),
                    'title' => __('Products'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab\Products')->toHtml()
                ]
            );

            $this->addTab(
                'orders_section',
                [
                    'label' => __('Orders to ship'),
                    'title' => __('Orders to ship'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab\OrdersToShip')->toHtml()
                ]
            );

            $this->addTab(
                'import_section',
                [
                    'label' => __('Import'),
                    'title' => __('Import'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab\Import')->toHtml()
                ]
            );

        }

        //raise event to add tabs
        $this->_eventManager->dispatch('bms_advancedstock_warehouse_edit_tabs', ['warehouse' => $this->getWarehouse(), 'tabs' => $this, 'layout' => $this->getLayout()]);


        return parent::_beforeToHtml();
    }
}
