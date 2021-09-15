<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\Indexer\Wished;

use Amasty\Sorting\Model\Indexer\AbstractIndexer;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;

class WishedIndexer extends AbstractIndexer
{
    /**
     * WishedIndexer constructor.
     *
     * @param \Amasty\Sorting\Model\ResourceModel\Method\Wished $wishedMethod
     * @param \Amasty\Sorting\Helper\Data                       $helper
     */
    public function __construct(
        \Amasty\Sorting\Model\ResourceModel\Method\Wished $wishedMethod,
        \Amasty\Sorting\Helper\Data $helper,
        CacheTypeListInterface $cache
    ) {
        parent::__construct($wishedMethod, $helper, $cache);
    }
}
