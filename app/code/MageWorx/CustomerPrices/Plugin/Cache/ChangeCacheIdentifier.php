<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Plugin\Cache;

use MageWorx\CustomerPrices\Model\CacheIdentifier;

class ChangeCacheIdentifier
{
    /**
     * @var CacheIdentifier
     */
    protected $cacheIdentifier;

    /**
     * ChangeCacheIdentifier constructor.
     *
     * @param CacheIdentifier $cacheIdentifier
     */
    public function __construct(
        CacheIdentifier $cacheIdentifier
    ) {
        $this->cacheIdentifier = $cacheIdentifier;
    }

    /**
     * @param \Magento\Framework\App\Http\Context $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetData(\Magento\Framework\App\Http\Context $subject, $result)
    {
        return $this->cacheIdentifier->addCustomerDate($result);
    }
}