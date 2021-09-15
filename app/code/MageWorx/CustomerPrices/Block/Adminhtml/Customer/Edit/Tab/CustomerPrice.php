<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type as ProductType;
use MageWorx\CustomerPrices\Block\Adminhtml\Customer\Edit\Tab\CustomerPrice\Grid\Column\Renderer\CustomPrice;
use MageWorx\CustomerPrices\Block\Adminhtml\Customer\Edit\Tab\CustomerPrice\Grid\Column\Renderer\CustomSpecialPrice;
use MageWorx\CustomerPrices\Block\Adminhtml\Customer\Edit\Tab\CustomerPrice\Grid\Column\Renderer\SpecialPrice;
use MageWorx\CustomerPrices\Helper\Base as HelperBase;

class CustomerPrice extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var ProductType
     */
    protected $type;

    /**
     * @var \MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices
     */
    protected $customerPriceResourceModel;

    /**
     * @var HelperBase
     */
    protected $helperBase;

    /**
     * CustomerPrice constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductType $type
     * @param Status $status
     * @param \MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices $customerPriceResourceModel
     * @param HelperBase $helperBase
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        ProductCollectionFactory $productCollectionFactory,
        ProductType $type,
        Status $status,
        \MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices $customerPriceResourceModel,
        HelperBase $helperBase,
        array $data = []
    ) {
        $this->productCollectionFactory   = $productCollectionFactory;
        $this->type                       = $type;
        $this->status                     = $status;
        $this->customerPriceResourceModel = $customerPriceResourceModel;
        $this->helperBase                 = $helperBase;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customerprices_grid_product_price');
        $this->setDefaultSort('entity_id');
        $this->setDefaultFilter(['in_custom_price' => 1]);
        $this->setUseAjax(true);
    }

    /**
     * @param Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_custom_price') {
            $productIds = $this->getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $linkField = 'entity_id';
            $filter    = $column->getFilter();
            if ($filter !== false && $column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter($linkField, ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter($linkField, ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareCollection()
    {
        $collection = $this->productCollectionFactory->create()->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'status'
        )->addAttributeToSelect(
            'price'
        )->addAttributeToSelect(
            'special_price'
        )->addAttributeToSelect(
            'type_id'
        );

        $collection->addAttributeToFilter('type_id', ['in' => $this->helperBase->getAllowedProductTypes()]);

        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId > 0) {
            $collection->addStoreFilter($storeId);
        }

        $collection = $this->customerPriceResourceModel->joinMageWorxCustomerPricesCollect($collection);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $baseCurrencyCode = (string)$this->_scopeConfig->getValue(
            \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->addColumn(
            'in_custom_price',
            [
                'type'             => 'checkbox',
                'name'             => 'in_custom_price',
                'values'           => $this->getSelectedProducts(),
                'index'            => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        )->addColumn(
            'entity_id',
            [
                'header'           => __('ID'),
                'sortable'         => true,
                'index'            => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        )->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index'  => 'name'
            ]
        )->addColumn(
            'type',
            [
                'header'           => __('Type'),
                'index'            => 'type_id',
                'type'             => 'options',
                'options'          => $this->type->getOptionArray(),
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type'
            ]
        )->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index'  => 'sku'
            ]
        )->addColumn(
            'price',
            [
                'header'        => __('Price'),
                'type'          => 'currency',
                'currency_code' => $baseCurrencyCode,
                'index'         => 'price'
            ]
        )->addColumn(
            'special_price',
            [
                'header'   => __('Special Price'),
                'renderer' => SpecialPrice::class,
                'align'    => 'center',
                'index'    => 'special_price'
            ]
        )->addColumn(
            'customer_price',
            [
                'header'   => __('Customer Price'),
                'renderer' => CustomPrice::class,
                'align'    => 'center',
                'sortable' => true
            ]
        )->addColumn(
            'customer_special_price',
            [
                'header'   => __('Customer Special Price'),
                'renderer' => CustomSpecialPrice::class,
                'align'    => 'center',
                'sortable' => true
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mageworx_customerprices/customer_product/grid', ['_current' => true]);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getSelectedProducts()
    {
        $params = $this->getRequest()->getParams();
        if (!empty($params['selected_products']) && is_array($params['selected_products'])) {
            $selectedProductIds = array_combine(
                array_values($params['selected_products']),
                array_values($params['selected_products'])
            );
        } else {
            if (!empty($params['id'])) {
                $productIds         = $this->customerPriceResourceModel->getProductIdsByCustomerId($params['id']);
                $selectedProductIds = array_combine(array_values($productIds), array_values($productIds));
            } else {
                $selectedProductIds = [];
            }
        }

        return $selectedProductIds;
    }

    /**
     * @return int
     */
    public function getSelectedProductsCount()
    {
        return count($this->getSelectedProducts());
    }


}