<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Akeans\Pocustomize\Model\ResourceModel\Popayment;
/**
 * Order configuration model
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Akeans\Pocustomize\Model\Popayment', 'Akeans\Pocustomize\Model\ResourceModel\Popayment');
    }
}
