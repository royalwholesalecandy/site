<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab;

use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices\CollectionFactory;
use MageWorx\CustomerPrices\Helper\Base as HelperBase;
use Magento\Catalog\Model\Product\Type as ProductTypes;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CustomerPrices
     */
    protected $customerPriceResourceModel;

    /**
     * @var HelperBase
     */
    protected $helperBase;

    /**
     * @var String|null
     */
    protected $productType = null;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param HelperBase $helperBase
     * @param CustomerPrices $customerPriceResourceModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CollectionFactory $collectionFactory,
        HelperBase $helperBase,
        CustomerPrices $customerPriceResourceModel,
        array $data = []
    ) {
        $this->collectionFactory          = $collectionFactory;
        $this->helperBase                 = $helperBase;
        $this->customerPriceResourceModel = $customerPriceResourceModel;
        $this->productType                = $this->helperBase->getProductType();

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customerPricesGrid');
        $this->setDefaultSort('customer_id');
        $this->setDefaultDir('desc');

        $this->setUseAjax(true);

        $this->setEmptyText(__('No Prices per Customer'));
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareCollection()
    {
        if (!in_array($this->productType, $this->helperBase->getAllowedProductTypes())) {
            return parent::_prepareCollection();
        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('product_id', $this->helperBase->getProductId());
        $collection = $this->customerPriceResourceModel->joinEmailCustomer($collection);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        if (!in_array($this->productType, $this->helperBase->getAllowedProductTypes())) {
            return parent::_prepareColumns();
        }

        $this->addColumn(
            'customer_id',
            [
                'header'   => __('Customer ID'),
                'align'    => 'left',
                'index'    => 'customer_id',
                'renderer' =>
                    '\MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab\Grid\Renderer\CustomerId',
                'width'    => '10px;'
            ]
        );

        $this->addColumn(
            'customer_email',
            [
                'header' => __('Customer Email'),
                'type'   => 'text',
                'align'  => 'center',
                'index'  => 'email',
                'width'  => '70px;',
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type'   => 'text',
                'align'  => 'right',
                'index'  => 'price',
                'width'  => '15px;',
            ]
        );

        $this->addColumn(
            'special_price',
            [
                'header' => __('Special Price'),
                'type'   => 'text',
                'align'  => 'right',
                'index'  => 'special_price',
                'width'  => '15px;',
            ]
        );

        $this->addColumn(
            'edit',
            [
                'header'           => __('Edit'),
                'type'             => 'text',
                'filter'           => false,
                'sortable'         => false,
                'renderer'         =>
                    '\MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab\Grid\Renderer\Edit',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        $this->addColumn(
            'delete',
            [
                'header'           => __('Delete'),
                'type'             => 'text',
                'filter'           => false,
                'renderer'         =>
                    '\MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab\Grid\Renderer\Delete',
                'sortable'         => false,
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('mageworx_customerprices/*/customerpricesgrid', ['_current' => true]);
    }
}
