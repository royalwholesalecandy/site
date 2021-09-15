<?php

namespace BoostMyShop\Supplier\Block\Replenishment\Renderer;


class QtyToOrder extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $productId = $row->getentity_id();

        $min = max($row->getqty_for_backorder() - $row->getqty_to_receive(), 0);
        $max = max($row->getqty_for_backorder() + $row->getqty_for_low_stock() - $row->getqty_to_receive(), 0);

        $html = '<table border="0"><tr>';
        $html .= '<td><input type="button" class="btn_min_qty" value="'.$min.'" onclick="jQuery(\'#qty_'.$productId.'\').val('.$min.'); updateQty('.$productId.'); "></td>';
        $html .= '<td><input type="text" id="qty_'.$productId.'" name="qty['.$productId.']" value="" size="3" onchange="updateQty('.$productId.');"></td>';
        $html .= '<td><input type="button" class="btn_max_qty" value="'.$max.'" onclick="jQuery(\'#qty_'.$productId.'\').val('.$max.'); updateQty('.$productId.');"></td>';
        $html .= '</tr></table>';

        return $html;
    }

}