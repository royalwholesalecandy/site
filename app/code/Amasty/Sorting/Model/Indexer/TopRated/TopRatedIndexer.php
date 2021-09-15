<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\Indexer\TopRated;

use Amasty\Sorting\Model\Indexer\AbstractIndexer;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;
use Amasty\Sorting\Model\ResourceModel\Method\Toprated;
use Amasty\Sorting\Helper\Data;

class TopRatedIndexer extends AbstractIndexer
{
    public function __construct(
        Toprated $topratedMethod,
        Data $helper,
        CacheTypeListInterface $cache
    ) {
        parent::__construct($topratedMethod, $helper, $cache);
    }
}
