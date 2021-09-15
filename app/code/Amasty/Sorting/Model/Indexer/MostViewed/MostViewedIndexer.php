<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\Indexer\MostViewed;

use Amasty\Sorting\Model\Indexer\AbstractIndexer;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;

class MostViewedIndexer extends AbstractIndexer
{
    /**
     * MostViewedIndexer constructor.
     *
     * @param \Amasty\Sorting\Model\ResourceModel\Method\MostViewed $wishedMethod
     * @param \Amasty\Sorting\Helper\Data                           $helper
     */
    public function __construct(
        \Amasty\Sorting\Model\ResourceModel\Method\MostViewed $wishedMethod,
        \Amasty\Sorting\Helper\Data $helper,
        CacheTypeListInterface $cache
    ) {
        parent::__construct($wishedMethod, $helper, $cache);
    }
}
