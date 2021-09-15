<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Indexer\Model\IndexerFactory;
use Amasty\Sorting\Model\Indexer\Bestsellers\BestsellersProcessor;
use Amasty\Sorting\Model\Indexer\MostViewed\MostViewedProcessor;
use Amasty\Sorting\Model\Indexer\TopRated\TopRatedProcessor;
use Amasty\Sorting\Model\Indexer\Wished\WishedProcessor;
use Magento\Framework\App\State;

class InstallData implements InstallDataInterface
{
    /**
     * @var IndexerFactory
     */
    private $indexer;

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
     * @var \Magento\Framework\App\State
     */
    private $state;

    public function __construct(
        IndexerFactory $indexer,
        State $state
    ) {
        $this->state = $state;
        $this->indexer = $indexer;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->state->emulateAreaCode(
            'adminhtml',
            [$this, 'reindexAll']
        );
    }

    public function reindexAll()
    {
        foreach ($this->indexerIds as $indexerId) {
            $indexer = $this->indexer->create()
                ->load($indexerId);
            $indexer->reindexAll();
        }
    }
}
