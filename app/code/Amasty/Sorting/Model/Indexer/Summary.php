<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\Indexer;

use Amasty\Sorting\Helper\Data;
use Amasty\Sorting\Model\Indexer\Bestsellers\BestsellersProcessor;
use Amasty\Sorting\Model\Indexer\MostViewed\MostViewedProcessor;
use Amasty\Sorting\Model\Indexer\TopRated\TopRatedProcessor;
use Amasty\Sorting\Model\Indexer\Wished\WishedProcessor;
use Magento\Indexer\Model\IndexerFactory;

/**
 * Class Summary
 */
class Summary
{
    /**
     * @var array
     */
    private $indexerIds = [
        BestsellersProcessor::INDEXER_ID,
        MostViewedProcessor::INDEXER_ID,
        TopRatedProcessor::INDEXER_ID,
        WishedProcessor::INDEXER_ID
    ];

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Amasty\Sorting\Model\MethodProvider
     */
    private $methodProvider;

    /**
     * @var IndexerFactory
     */
    private $indexerFactory;

    public function __construct(
        \Amasty\Sorting\Helper\Data $helper,
        \Amasty\Sorting\Model\MethodProvider $methodProvider,
        IndexerFactory $indexerFactory
    ) {
        $this->helper = $helper;
        $this->methodProvider = $methodProvider;
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * @return void
     */
    public function reindexAll()
    {
        foreach ($this->indexerIds as $indexerId) {
            $indexer = $this->indexerFactory->create()
                ->load($indexerId);
            // do full reindex if method not disabled
            $indexer->reindexAll();
        }
    }
}
