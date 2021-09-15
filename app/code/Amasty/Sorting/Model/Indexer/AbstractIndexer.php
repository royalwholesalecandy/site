<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\Indexer;

use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;

abstract class AbstractIndexer implements IndexerActionInterface, MviewActionInterface
{
    /**
     * @var \Amasty\Sorting\Api\IndexedMethodInterface
     */
    protected $indexBuilder;

    /**
     * @var \Amasty\Sorting\Helper\Data
     */
    protected $helper;

    /**
     * @var CacheTypeListInterface
     */
    protected $cache;

    /**
     * AbstractIndexer constructor.
     *
     * @param \Amasty\Sorting\Api\IndexedMethodInterface $indexBuilder  for parent class
     * @param \Amasty\Sorting\Helper\Data                $helper
     */
    public function __construct(
        \Amasty\Sorting\Api\IndexedMethodInterface $indexBuilder,
        \Amasty\Sorting\Helper\Data $helper,
        CacheTypeListInterface $cache
    ) {
        $this->indexBuilder = $indexBuilder;
        $this->helper = $helper;
        $this->cache = $cache;
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
        // do full reindex if method is not disabled
        if (!$this->helper->isMethodDisabled($this->indexBuilder->getMethodCode())) {
            $this->indexBuilder->reindex();
            $this->cache->invalidate('full_page');
        }
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
        if (!$ids) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not rebuild index for empty products array')
            );
        }
        $this->doExecuteList($ids);
    }

    /**
     * Execute partial indexation by ID list. Template method
     *
     * @param int[] $ids
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doExecuteList($ids)
    {
        $this->executeFull();
    }

    /**
     * Execute partial indexation by ID. Template method
     *
     * @param int $id
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doExecuteRow($id)
    {
        $this->executeFull();
    }

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
}
