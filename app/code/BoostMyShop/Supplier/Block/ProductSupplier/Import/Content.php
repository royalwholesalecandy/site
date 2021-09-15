<?php
namespace BoostMyShop\Supplier\Block\ProductSupplier\Import;

class Content extends \Magento\Backend\Block\Template
{
    protected $_template = 'ProductSupplier/Import/Content.phtml';

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/processImport');
    }

}