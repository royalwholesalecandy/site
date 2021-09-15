<?php

namespace BoostMyShop\OrderPreparation\Helper;

class Compatibility
{

    protected $_moduleManager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager
    ){
        $this->_moduleManager = $moduleManager;
    }

    public function hasFoomanPdfCustomizerInstalled()
   {
       return $this->_moduleManager->isEnabled('Fooman_PdfCustomiser');
   }

}