<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Qty extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        $moq  = $row->getMoq();

        $value = $row->getpop_qty();
        $html = '<input size="6"
                        type="textbox"
                        name="products['.$row->getId().'][qty]"
                        onchange="order.saveField('.$row->getpop_po_id().','.$row->getpop_id().',\'pop_qty\', this.value)"
                        id="products['.$row->getId().'][qty]"
                        value="'.$value.'">';
        
        $unitQty = $row->getpop_qty() * $row->getpop_qty_pack();
        if($this->_config->getSetting('general/pack_quantity')){
            $html.= "<br><span style='font-style: italic;'>".$unitQty." units<span>";
        }

        if($moq>0){
        	$color = '';
            if($this->_config->getSetting('general/pack_quantity')){
                if($moq > $unitQty){
                    $color = "RED";
                }
            } else {
                if($moq > $row->getpop_qty()){
                    $color = "RED";
                }
            }
        	
        	$html.= "<br><span style='font-style: italic; color: ".$color.";'>MOQ: ".$moq."<span>";
        }
        return $html;
    }
}