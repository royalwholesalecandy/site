<?php
/**
 * Copyright © Royal. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Royal\CustomShipPrice\Helper;

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
        return $this->scopeConfig->getValue('carriers/customshipprice/'.$key);
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
            ->getValue('royal_customshipprice/general/' . $key);
    }
}
