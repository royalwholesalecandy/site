<?php

namespace BoostMyShop\Supplier\Helper;

class Logger
{

    const kLogGeneral = 'general';

    protected $_config;

    public function __construct(
        \BoostMyShop\Supplier\Model\Config $config
    )
    {
        $this->_config = $config;
    }

    public function log($msg, $type = self::kLogGeneral)
    {
        if ($this->_config->getSetting('general/disable_log'))
            return;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/supplier_'.$type.'.log');

        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($msg);
    }

    public function logException($exception, $type = self::kLogGeneral)
    {
        $msg= $exception->getMessage().' : '.$exception->getTraceAsString();
        $this->log($msg, $type);
    }

}