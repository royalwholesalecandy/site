<?php
/**
 * Copyright © 2015 Megnor. All rights reserved.
 */

namespace Megnor\ShopByBrand\Model;

class Items extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Megnor\ShopByBrand\Model\Resource\Items');
    }
}
