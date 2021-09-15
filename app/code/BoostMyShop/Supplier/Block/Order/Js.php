<?php
namespace BoostMyShop\Supplier\Block\Order;

class Js extends \Magento\Backend\Block\Template
{
    protected $_template = 'Order/Js.phtml';


    public function getSaveFieldUrl()
    {
        return $this->getUrl('*/*/SaveOrderProductField');
    }

}