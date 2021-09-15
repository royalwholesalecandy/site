<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Indexer;

use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Indexer\CacheContext;

abstract class AbstractIndexer implements IndexerActionInterface, MviewActionInterface, IdentityInterface
{
    /**
     * @var IndexBuilder
     */
    protected $indexBuilder;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cacheManager;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    protected $cacheContext;

    /**
     * AbstractIndexer constructor.
     * @param IndexBuilder $indexBuilder
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\CacheInterface $cacheManager
     */
    public function __construct(
        IndexBuilder $indexBuilder,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\CacheInterface $cacheManager
    ) {
        $this->indexBuilder = $indexBuilder;
        $this->_eventManager = $eventManager;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->indexBuilder->reindexFull();
        $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
        $this->cacheManager->clean($this->getIdentities());
    }

    /**
     * Get affected cache tags
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getIdentities()
    {
        return [
            \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
        ];
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->doExecuteList($ids);
    }

    /**
     * Execute partial indexation by ID list. Template method
     *
     * @param int[] $ids
     * @return void
     */
    abstract protected function doExecuteList($ids);

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function executeRow($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }

        $this->doExecuteRow($id);
    }

    /**
     * executeByQueue
     */
    public function executeByQueue()
    {
        $this->doExecuteByQueue();
    }

    /**
     * Execute partial indexation by ID. Template method
     *
     * @param int $id
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    abstract protected function doExecuteRow($id);
}
