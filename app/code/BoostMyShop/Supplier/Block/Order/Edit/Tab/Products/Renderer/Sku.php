<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Sku extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        if ($this->_config->isErpIsInstalled())
            $url = $this->getUrl('erp/products/edit', ['id' => $row->getpop_product_id()]);
        else
            $url = $this->getUrl('catalog/product/edit', ['id' => $row->getpop_product_id()]);

        $html = '<a href="'.$url.'">'.$row->getpop_sku().'</a>';

        return $html;
    }
}