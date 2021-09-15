<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Plugin\Cache;

use MageWorx\CustomerPrices\Model\CacheIdentifier;

class ChangeWidgetCacheIdentifier
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
     * @param \Magento\Framework\View\Element\AbstractBlock $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetCacheKeyInfo(\Magento\Framework\View\Element\AbstractBlock $subject, $result)
    {
        return $this->cacheIdentifier->addCustomerDate($result);
    }
}