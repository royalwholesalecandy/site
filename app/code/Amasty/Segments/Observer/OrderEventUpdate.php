<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Observer;

class OrderEventUpdate extends \Amasty\Segments\Observer\AbstractEventObserver
{
    /**
     * @var string
     */
    protected $type = 'order';
}
