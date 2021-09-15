<?php

namespace BoostMyShop\AdvancedStock\Helper;

class Logger
{

    const kLogReservation = 'reservation';
    const kLogInventory = 'inventory';
    const kLogShipment = 'shipment';
    const kLogGeneral = 'general';
    const kLogRouting = 'routing';
    const kLogInventoryCore = 'inventory_core';

    protected $_config;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Config $config
    )
    {
        $this->_config = $config;
    }

    public function log($msg, $type = self::kLogGeneral)
    {
        if ($this->_config->getSetting('general/disable_log'))
            return;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/advancedstock_'.$type.'.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($msg);
    }

}