<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\Indexer\Bestsellers\System\Config;

use Amasty\Sorting\Model\Indexer\TopRated\TopRatedProcessor;
use Amasty\Sorting\Model\Indexer\ConfigInvalidateAbstract;

class TopRatedInvalidate extends ConfigInvalidateAbstract
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        TopRatedProcessor $indexProcessor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $indexProcessor, // toprated index processor
            $resource,
            $resourceCollection,
            $data
        );
    }
}
