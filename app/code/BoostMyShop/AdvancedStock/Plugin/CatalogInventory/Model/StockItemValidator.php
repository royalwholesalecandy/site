<?php

namespace BoostMyShop\AdvancedStock\Plugin\CatalogInventory\Model;

//Magento 2.2 compatibility
class StockItemValidator
{
    protected $_logger;

    public function __construct (
        \BoostMyShop\AdvancedStock\Helper\Logger $logger
    )
    {
        $this->_logger = $logger;
    }

    public function aroundValidate(\Magento\CatalogInventory\Model\StockItemValidator $subject, $proceed, $product, $stockItem)
    {
        $this->_logger->log('Skip stock item validation', \BoostMyShop\AdvancedStock\Helper\Logger::kLogGeneral);
    }

}