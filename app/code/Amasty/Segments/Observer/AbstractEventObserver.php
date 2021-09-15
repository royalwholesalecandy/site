<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Observer;

use Magento\Framework\Event\ObserverInterface;

abstract class AbstractEventObserver implements ObserverInterface
{
    const EXPLODE_DELIMITER = '_';

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var \Amasty\Segments\Model\Indexer\IndexerQueue
     */
    private $indexerQueue;

    /**
     * AbstractEventObserver constructor.
     * @param \Amasty\Segments\Model\Indexer\IndexerQueue $indexerQueue
     */
    public function __construct(
        \Amasty\Segments\Model\Indexer\IndexerQueue $indexerQueue
    ) {
        $this->indexerQueue = $indexerQueue;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $eventType = $this->getTypeByEventName($observer->getEvent()->getName());
        $this->indexerQueue->eventUpdate($eventType);

        return $this;
    }

    /**
     * @param $eventName
     * @return string
     */
    public function getTypeByEventName($eventName)
    {
        return $this->type;
    }
}
