<?php
namespace BoostMyShop\Erp\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;

class ErpView extends Generic
{
 

    public function getButtonData()
    {
        $url = $this->getUrl('erp/products/edit', ['id' => $this->getProduct()->getId()]);

        return [
            'label' => __('Switch to ERP View'),
            'on_click' => "document.location.href='".$url."';",
            'sort_order' => 100
        ];
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

}