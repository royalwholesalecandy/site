<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\CustomShippingPrice\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Get carrier settings
     *
     * @param $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->scopeConfig->getValue('carriers/customshipping/'.$key);
    }

    /**
     * Get module settings
     *
     * @param $key
     * @return mixed
     */
    public function getConfigModule($key)
    {
        return $this->scopeConfig
            ->getValue('mageside_customshippingprice/general/' . $key);
    }
}
