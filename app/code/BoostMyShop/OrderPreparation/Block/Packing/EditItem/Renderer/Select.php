<?php

namespace BoostMyShop\OrderPreparation\Block\Packing\EditItem\Renderer;

use Magento\Framework\DataObject;

class Select extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{


    public function render(DataObject $row)
    {
        $html = '<input type="button" value="Select" onclick="packingObj.selectSubstitutionProduct('.$row->getId().', \''.$row->getSku().'\', \''.$row->getName().'\');">';
        return $html;
    }
}