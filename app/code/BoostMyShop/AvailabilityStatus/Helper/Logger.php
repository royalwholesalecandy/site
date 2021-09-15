<?php namespace BoostMyShop\AvailabilityStatus\Helper;

use BoostMyShop\AvailabilityStatus\Model\Config;

class Logger
{
    protected $_config;

    const kLogGeneral = 'general';

    public function __construct(Config $config)
    {
        $this->_config = $config;
    }

    public function log($msg, $type = self::kLogGeneral)
    {
        if(!$this->_config->getSetting('general/enable_logs')){
            return;
        }

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/availabilitystatus_'.$type.'.log');
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
