<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab\Form\CustomerSearch;

/**
 * Adminhtml Customer Prices grid block
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customerSearchGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');

        $this->setUseAjax(true);

        $this->setEmptyText(__('No Customers'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
             'header' => __('Customer ID'),
             'align' => 'left',
             'index' => 'entity_id',
             'width' => '10px;'
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('Customer Email'),
                'type' => 'text',
                'align' => 'center',
                'index' => 'email',
                'width' => '70px;',
            ]
        );

        $this->addColumn(
            'firstname',
            [
                'header' => __('First Name'),
                'type' => 'text',
                'align' => 'right',
                'index' => 'firstname',
                'width' => '50px;',
            ]
        );

        $this->addColumn(
            'lastname',
            [
                'header' => __('Last Name'),
                'type' => 'text',
                'align' => 'right',
                'index' => 'lastname',
                'width' => '50px;',
            ]
        );

        $this->addColumn(
            'select',
            [
                'header' => __('Select'),
                'type'   => 'text',
                'filter' => false,
                'sortable' => false,
                'renderer' =>
         '\MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab\Form\CustomerSearch\Grid\Renderer\Select',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mageworx_customerprices/*/customersearchgrid', ['_current' => true]);
    }

    /**
     * @return bool|string
     */
    public function getRowClickCallback()
    {
        return false;
    }
}