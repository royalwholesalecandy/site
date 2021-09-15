<?php

namespace BoostMyShop\Supplier\Block;


class Invoice extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected $_resourceModel;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addData(
            [
                \Magento\Backend\Block\Widget\Container::PARAM_CONTROLLER => 'invoice',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BLOCK_GROUP => 'BoostMyShop_Supplier',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BUTTON_NEW => __('Add New Invoice'),
                \Magento\Backend\Block\Widget\Container::PARAM_HEADER_TEXT => __('Supplier Invoices'),
            ]
        );
        parent::_construct();
        $this->_addNewButton();
    }
}
