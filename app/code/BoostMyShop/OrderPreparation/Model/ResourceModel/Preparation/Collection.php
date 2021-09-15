<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BoostMyShop\OrderPreparation\Model\ResourceModel\Preparation;

use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;

/**
 * Flat sales order collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    public function addStatusesFilter($statuses)
    {
        $this->addAttributeToFilter('status', array('in' => $statuses));
        return $this;
    }
}