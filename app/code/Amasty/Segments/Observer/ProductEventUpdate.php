<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Observer;

class ProductEventUpdate extends \Amasty\Segments\Observer\AbstractEventObserver
{
    /**
     * @var string
     */
    protected $type = 'product';

    /**
     * @param $eventName
     * @return string
     */
    public function getTypeByEventName($eventName)
    {
        switch ($eventName) {
            case 'wishlist_items_renewed':
                return 'wishlist';
            case 'catalog_controller_product_view':
                return 'viewed';
        }

        return parent::getTypeByEventName($eventName);
    }
}
