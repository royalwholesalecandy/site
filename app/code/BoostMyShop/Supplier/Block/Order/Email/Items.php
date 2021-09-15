<?php

namespace BoostMyShop\Supplier\Block\Order\Email;

class Items extends \Magento\Framework\View\Element\Template
{
    public function getPurchaseOrder()
    {
        return $this->getData('order');
    }

}
