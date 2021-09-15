<?php

namespace BoostMyShop\Erp\Block\Products\Edit;

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
        $this->setTitle(__('Product details'));
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

    protected function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    protected function _beforeToHtml()
    {
        if (!$this->getRequest()->getParam('active_tab'))
            $this->setActiveTab('overview');

        parent::_beforeToHtml();
    }

}
