<?php

namespace BoostMyShop\Supplier\Block\ProductSupplier\Renderer;

use Magento\Framework\DataObject;

class LastBuyingPrice extends AbstractRenderer
{


    public function render(DataObject $row)
    {
        if ($row->getsp_id() && $row->getsp_last_buying_price() > 0) {
            $value = $row->getsp_last_buying_price();
            return '<div align="right">'.$value.'</div>';
        }
    }

    public function renderExport(DataObject $row)
    {
        return $row->getsp_base_price();
    }

}