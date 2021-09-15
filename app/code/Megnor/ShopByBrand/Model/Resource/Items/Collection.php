<?php
/**
 * Copyright © 2015 Megnor. All rights reserved.
 */

namespace Megnor\ShopByBrand\Model\Resource\Items;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Megnor\ShopByBrand\Model\Items', 'Megnor\ShopByBrand\Model\Resource\Items');
    }
}
