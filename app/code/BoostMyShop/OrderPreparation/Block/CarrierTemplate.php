<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BoostMyShop\OrderPreparation\Block;

/**
 * User block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class CarrierTemplate extends \Magento\Backend\Block\Widget\Grid\Container
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
        //$this->_resourceModel = $resourceModel;
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
                \Magento\Backend\Block\Widget\Container::PARAM_CONTROLLER => 'carrierTemplate',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BLOCK_GROUP => 'BoostMyShop_OrderPreparation',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BUTTON_NEW => __('Add New template'),
                \Magento\Backend\Block\Widget\Container::PARAM_HEADER_TEXT => __('Shipping Label Template'),
            ]
        );
        parent::_construct();
        $this->_addNewButton();
    }
}
