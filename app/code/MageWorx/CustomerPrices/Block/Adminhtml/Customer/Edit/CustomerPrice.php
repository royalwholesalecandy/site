<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Block\Adminhtml\Customer\Edit;

use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices;

class CustomerPrice extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'customer/edit/customerprices/customers.phtml';

    /**
     * Block Grid
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageWorx\CustomerPrices\Model\Encoder
     */
    protected $jsonEncoder;

    /**
     * @var CustomerPrices
     */
    protected $customerResourceModel;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\CustomerPrices\Model\Encoder $jsonEncoder
     * @param CustomerPrices $customerResourceModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MageWorx\CustomerPrices\Model\Encoder $jsonEncoder,
        CustomerPrices $customerResourceModel,
        array $data = []
    ) {
        $this->registry              = $registry;
        $this->jsonEncoder           = $jsonEncoder;
        $this->customerResourceModel = $customerResourceModel;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \MageWorx\CustomerPrices\Block\Adminhtml\Customer\Edit\Tab\CustomerPrice::class,
                'customer.products.grid'
            );
        }

        return $this->blockGrid;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Customer Price');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsJson()
    {
        $params = $this->getRequest()->getParams();
        if (!empty($params['id'])) {
            $productIds         = $this->customerResourceModel->getProductIdsByCustomerId($params['id']);
            $selectedProductIds = array_combine(array_values($productIds), array_values($productIds));
        } else {
            $selectedProductIds = [];
        }

        return $this->jsonEncoder->encode($selectedProductIds);
    }

    /**
     * example 5:{price:-10%,special_price:-20%}
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsPriceJson()
    {
        $params = $this->getRequest()->getParams();
        if (!empty($params['id'])) {
            $productPriceData = $this->customerResourceModel->getProductsPricesByCustomerId($params['id']);
        } else {
            $productPriceData = [];
        }

        return $this->jsonEncoder->encode($productPriceData);
    }

    /**
     * @return string
     */
    public function getFieldId()
    {
        return 'in_products';
    }

    /**
     * @return string
     */
    public function getFieldPriceId()
    {
        return 'in_custom_price_products';
    }
}