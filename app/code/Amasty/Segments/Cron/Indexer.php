<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Cron;

class Indexer
{
    /**
     * @var \Amasty\Segments\Model\Indexer\SegmentCustomerIndexer
     */
    private $segmentCustomerIndexer;

    /**
     * Indexer constructor.
     * @param \Amasty\Segments\Model\Indexer\SegmentCustomerIndexer $segmentCustomerIndexer
     */
    public function __construct(\Amasty\Segments\Model\Indexer\SegmentCustomerIndexer $segmentCustomerIndexer)
    {
        $this->segmentCustomerIndexer = $segmentCustomerIndexer;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->segmentCustomerIndexer->doExecuteByQueue();
    }
}
