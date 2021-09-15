<?php
namespace BoostMyShop\OrderPreparation\Block\Shipping;

class Index extends \Magento\Backend\Block\Template
{
    protected $_template = 'OrderPreparation/Shipping/Index.phtml';

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

}