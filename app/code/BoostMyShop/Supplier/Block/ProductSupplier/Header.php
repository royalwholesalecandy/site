<?php
namespace BoostMyShop\Supplier\Block\ProductSupplier;

class Header extends \Magento\Backend\Block\Template
{
    protected $_template = 'ProductSupplier/Header.phtml';

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

    public function getPopupUrl()
    {
        return $this->getUrl('*/*/popup');
    }

    public function getImportUrl()
    {
        return $this->getUrl('*/*/import');
    }


}