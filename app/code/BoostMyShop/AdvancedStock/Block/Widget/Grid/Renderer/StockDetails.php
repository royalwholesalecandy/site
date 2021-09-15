<?php

namespace BoostMyShop\AdvancedStock\Block\Widget\Grid\Renderer;


class StockDetails extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_productHelper = null;
    protected $_warehouseItemCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollection,
        array $data = []
    ){
        parent::__construct($context, $data);

        $this->_warehouseItemCollectionFactory = $warehouseItemCollection;
    }

    public function render(\Magento\Framework\DataObject $product)
    {
        $html = [];

        $collection = $this->_warehouseItemCollectionFactory->create()->addProductFilter($product->getId())->joinWarehouse();
        foreach($collection as $item)
        {
            if (!$item->getwi_available_quantity() && !$item->getwi_physical_quantity())
                continue;

            $color = $this->getColor($item);
            $row = '<font color="'.$color.'">';
            $row .= $item->getw_name().' : '.$item->getwi_available_quantity().'/'.$item->getwi_physical_quantity();

            $details = [];
            if ($item->getwi_quantity_to_ship() > 0)
                $details[] = $item->getwi_quantity_to_ship().' to ship';
            if (count($details) > 0)
                $row .= ' <i>('.implode(', ', $details).')</i>';

            $row .= '</font>';
            $html[] = $row;
        }

        return implode('<br>', $html);
    }

}