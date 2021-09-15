<?php

namespace BoostMyShop\Erp\Helper;

class Boostmyshop
{
    protected $_moduleManager;

    public function __construct(
       \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->_moduleManager = $moduleManager;
    }


    public function advancedStockModuleIsInstalled()
    {
        return $this->_moduleManager->isEnabled('BoostMyShop_AdvancedStock');
    }

}