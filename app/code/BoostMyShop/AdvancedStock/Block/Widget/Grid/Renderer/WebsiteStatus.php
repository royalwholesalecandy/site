<?php

namespace BoostMyShop\AdvancedStock\Block\Widget\Grid\Renderer;


class WebsiteStatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_stockWebsiteCollectionFactory = null;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\StockWebsite\CollectionFactory $stockWebsiteCollectionFactory,
        array $data = []
    ){
        parent::__construct($context, $data);

        $this->_stockWebsiteCollectionFactory = $stockWebsiteCollectionFactory;
    }

    public function render(\Magento\Framework\DataObject $product)
    {
        $html = [];

        $collection = $this->_stockWebsiteCollectionFactory->create()->addProductFilter($product->getId());
        foreach($collection as $item)
        {
            $html[] = $item->getName().': '.($item->getindex_stock_status() ? __('in stock') : __('out of stock'));
        }

        return implode('<br>', $html);
    }

}