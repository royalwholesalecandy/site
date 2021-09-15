<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\Indexer\Bestsellers;

use Amasty\Sorting\Model\Indexer\AbstractIndexer;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;

class BestsellersIndexer extends AbstractIndexer
{
    public function __construct(
        \Amasty\Sorting\Model\ResourceModel\Method\Bestselling $bestsellingMethod,
        \Amasty\Sorting\Helper\Data $helper,
        CacheTypeListInterface $cache
    ) {
        parent::__construct($bestsellingMethod, $helper, $cache);
    }
}
