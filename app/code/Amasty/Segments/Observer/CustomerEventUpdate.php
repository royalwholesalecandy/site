<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Observer;

class CustomerEventUpdate extends \Amasty\Segments\Observer\AbstractEventObserver
{
    /**
     * @var string
     */
    protected $type = 'customer';

    /**
     * @param $eventName
     * @return string
     */
    public function getTypeByEventName($eventName)
    {
        $parsedEventName = explode(self::EXPLODE_DELIMITER, $eventName);

        return ($parsedEventName[1] == 'address')
            ? 'address' : parent::getTypeByEventName($eventName);
    }
}
