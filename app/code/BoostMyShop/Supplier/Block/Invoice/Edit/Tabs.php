<?php

namespace BoostMyShop\Supplier\Block\Invoice\Edit;

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
        $this->setTitle(__('Supplier Invoice'));
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

    protected function getInvoice()
    {
        return $this->_coreRegistry->registry('current_supplier_invoice');
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
                'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Invoice\Edit\Tab\Main')->toHtml(),
                'active' => true
            ]
        );
        
        if($this->getInvoice()->getId()){

            $this->addTab(
                'orders_section',
                [
                    'label' => __('Orders'),
                    'title' => __('Orders'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Invoice\Edit\Tab\Po')->toHtml()
                ]
            );

            if($this->getInvoice()->getBsiType() != 'creditmemo'){

                $this->addTab(
                    'payments_section',
                    [
                        'label' => __('Payments'),
                        'title' => __('Payments'),
                        'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Invoice\Edit\Tab\Payments')->toHtml()
                    ]
                );
            }
        }

        //raise event to add tabs
        $this->_eventManager->dispatch('bms_supplier_invoice_edit_tabs', ['invoice' => $this->getInvoice(), 'tabs' => $this, 'layout' => $this->getLayout()]);

        return parent::_beforeToHtml();
    }
}
