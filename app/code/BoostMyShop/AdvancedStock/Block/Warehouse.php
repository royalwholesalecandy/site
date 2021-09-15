<?php

namespace BoostMyShop\AdvancedStock\Block;


class Warehouse extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var \Magento\User\Model\ResourceModel\User
     */
    protected $_resourceModel;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\User\Model\ResourceModel\User $resourceModel
     * @param array $data
     */
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
                \Magento\Backend\Block\Widget\Container::PARAM_CONTROLLER => 'warehouse',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BLOCK_GROUP => 'BoostMyShop_Warehouse',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BUTTON_NEW => __('Add New Warehouse'),
                \Magento\Backend\Block\Widget\Container::PARAM_HEADER_TEXT => __('Warehouses'),
            ]
        );
        parent::_construct();
        $this->_addNewButton();
    }
}
