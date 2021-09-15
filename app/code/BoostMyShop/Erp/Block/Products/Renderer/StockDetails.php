<?php

namespace BoostMyShop\Erp\Block\Products\Renderer;


class StockDetails extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_replenishment = null;
    protected $_productHelper = null;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\Supplier\Model\Product $productHelper,
        array $data = []
    ){
        parent::__construct($context, $data);

        $this->_productHelper = $productHelper;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $productId = $row->getentity_id();
        return $this->_productHelper->getStockDetails($productId);
    }

}