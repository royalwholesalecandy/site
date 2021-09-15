<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Indexer;

class SegmentCustomerIndexer extends \Amasty\Segments\Model\Indexer\AbstractIndexer
{
    /**
     * {@inheritdoc}
     */
    protected function doExecuteList($ids)
    {
        $this->indexBuilder->reindexByIds($ids);
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteRow($id)
    {
        $this->indexBuilder->reindexById($id);
    }

    /**
     * doExecuteByQueue
     */
    public function doExecuteByQueue()
    {
        $this->indexBuilder->reindexByQueue();
    }

    /**
     * doExecuteFull
     */
    public function doExecuteFull()
    {
        $this->executeFull();
    }
}
