<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Block\Adminhtml\Deal;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price;

/**
 * Class ProductsGrid
 * @package Mageplaza\DailyDeal\Block\Adminhtml\Deal
 */
class ProductsGrid extends Extended
{
    /**
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ProductsGrid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param ProductFactory $productFactory
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductFactory $productFactory,
        Registry $coreRegistry,
        array $data = []
    )
    {
        $this->_productFactory = $productFactory;
        $this->_coreRegistry   = $coreRegistry;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productFactory->create()->getCollection();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('price');
        $collection->joinField(
            'barcode_qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1 AND {{table}}.website_id=0',
            'left'
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_product', [
            'type'             => 'radio',
            'html_name'        => 'products_id',
            'required'         => true,
            'align'            => 'center',
            'index'            => 'entity_id',
            'header_css_class' => 'col-select',
            'column_css_class' => 'col-select'
        ]);
        $this->addColumn('entity_id', [
            'header'           => __('Product ID'),
            'type'             => 'number',
            'index'            => 'entity_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
        ]);
        $this->addColumn('name', [
            'header'   => __('Name'),
            'index'    => 'name',
            'type'     => 'text',
            'sortable' => true
        ]);
        $this->addColumn('sku', [
            'header'   => __('Sku'),
            'index'    => 'sku',
            'type'     => 'text',
            'sortable' => true
        ]);
        $this->addColumn('barcode_qty', [
            'header' => __('Quantity'),
            'type'   => 'number',
            'index'  => 'barcode_qty'
        ]);
        $this->addColumn('price', [
            'header'           => __('Price'),
            'column_css_class' => 'price',
            'type'             => 'currency',
            'currency_code'    => $this->_storeManager->getStore()->getBaseCurrencyCode(),
            'index'            => 'price',
            'renderer'         => Price::class
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpdailydeal/deal/productsgrid', ['_current' => true]);
    }
}