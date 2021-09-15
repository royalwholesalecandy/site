<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Image extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        $imageHelper = $this->_imageHelper->init($row->getProduct(), 'product_listing_thumbnail');
        $imageUrl = $imageHelper->getUrl();

        if ($imageUrl)
            return '<img src="'.$imageUrl.'">';

    }
}