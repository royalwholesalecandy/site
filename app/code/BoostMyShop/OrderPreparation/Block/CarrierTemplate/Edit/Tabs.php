<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit;

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
        $this->setTitle(__('Information'));
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
        return $this->_coreRegistry->registry('current_carrier_template');
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
                'content' => $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Main')->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'export_section',
            [
                'label' => __('Export'),
                'title' => __('Export'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Export')->toHtml()
            ]
        );

        $this->addTab(
            'import_section',
            [
                'label' => __('Import'),
                'title' => __('Import'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Import')->toHtml()
            ]
        );

        $this->addTab(
            'helper_section',
            [
                'label' => __('Available codes'),
                'title' => __('Available codes'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Helper')->toHtml()
            ]
        );

        return parent::_beforeToHtml();
    }
}
