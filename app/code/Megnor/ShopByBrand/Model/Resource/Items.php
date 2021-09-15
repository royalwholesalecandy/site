<?php
/**
 * Copyright © 2015 Megnor. All rights reserved.
 */

namespace Megnor\ShopByBrand\Model\Resource;

class Items extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('megnor_shopbybrand_items', 'id');
    }
}
