<?php

namespace MageFix\Customer\Block\Adminhtml\Edit\Tab;

/**
 * Adminhtml customer orders grid block
 */
class Cart extends \Magento\Customer\Block\Adminhtml\Edit\Tab\Cart
{
    
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('MageFix_Customer::tab/cart.phtml');
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $quote = $this->getQuote();

        if ($quote) {
            $collection = $quote->getItemsCollection(false);
        } else {
            $collection = $this->_dataCollectionFactory->create();
        }

        $collection->addFieldToFilter('parent_item_id', ['null' => true]);

        $this->setCollection($collection);

        if ($collection->isLoaded()) {
            return $this;
        } else {
            return parent::_prepareCollection();
        }
    }
	protected function _prepareColumns()
    {
		
        $this->addColumn('product_id', ['header' => __('ID'), 'index' => 'product_id', 'width' => '100px']);

        $this->addColumn(
            'name',
            [
                'header' => __('Product'),
                'index' => 'name',
                'renderer' => \Magento\Customer\Block\Adminhtml\Edit\Tab\View\Grid\Renderer\Item::class
            ]
        );

        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku', 'width' => '100px']);
        $this->addColumn('weight', ['header' => __('Weight'), 'index' => 'weight', 'width' => '100px']);
        $this->addColumn('row_weight', ['header' => __('Total Weight'), 'index' => 'row_weight', 'width' => '100px']);

        $this->addColumn(
            'qty',
            ['header' => __('Quantity'), 'index' => 'qty', 'type' => 'number', 'width' => '60px']
        );
		
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'index' => 'price',
                'type' => 'currency',
                'currency_code' => $this->getQuote()->getQuoteCurrencyCode(),
                'rate' => $this->getQuote()->getBaseToQuoteRate(),
            ]
        );

        $this->addColumn(
            'total',
            [
                'header' => __('Total'),
                'index' => 'row_total',
                'type' => 'currency',
                'currency_code' => $this->getQuote()->getQuoteCurrencyCode(),
                'rate' => 1,
            ]
        );
		
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'index' => 'quote_item_id',
                'renderer' => \Magento\Customer\Block\Adminhtml\Grid\Renderer\Multiaction::class,
                'filter' => false,
                'sortable' => false,
                'actions' => [
                    [
                        'caption' => __('Configure'),
                        'url' => 'javascript:void(0)',
                        'process' => 'configurable',
                        'control_object' => $this->getJsObjectName() . 'cartControl',
                    ],
                    [
                        'caption' => __('Delete'),
                        'url' => '#',
                        'onclick' => 'return ' . $this->getJsObjectName() . 'cartControl.removeItem($item_id);'
                    ],
                ]
            ]
        );
		
        return parent::_prepareColumns();
    }

}
