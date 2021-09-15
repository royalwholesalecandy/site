<?php

namespace BoostMyShop\Supplier\Block;


class Order extends \Magento\Backend\Block\Widget\Grid\Container
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
                \Magento\Backend\Block\Widget\Container::PARAM_CONTROLLER => 'order',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BLOCK_GROUP => 'BoostMyShop_Supplier',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BUTTON_NEW => __('Create new Purchase Order'),
                \Magento\Backend\Block\Widget\Container::PARAM_HEADER_TEXT => __('Purchase Orders'),
            ]
        );
        parent::_construct();
        $this->_addNewButton();
    }

    public function getTitle()
    {
        return $this->getPoReference();
    }
}
