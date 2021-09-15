<?php

namespace BoostMyShop\UltimateReport\Helper;

class Logger
{

    const kLogGeneral = 'general';


    public function log($msg, $type = self::kLogGeneral)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/ultimatereport_'.$type.'.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($msg);
    }

}