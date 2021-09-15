<?php
namespace BoostMyShop\Supplier\Block\Supplier;

class Import extends \Magento\Backend\Block\Template
{
    protected $_template = 'Supplier/Import.phtml';

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/ProcessImport');
    }


}