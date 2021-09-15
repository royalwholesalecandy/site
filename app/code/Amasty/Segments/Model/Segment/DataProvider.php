<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Segment;

use Amasty\Segments\Model\ResourceModel\Segment\CollectionFactory;
use Amasty\Segments\Model\Segment;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Amasty\Segments\Model\SegmentRepository
     */
    protected $segmentRepository;

    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param \Amasty\Segments\Api\SegmentRepositoryInterface $segmentRepository
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Amasty\Segments\Api\SegmentRepositoryInterface $segmentRepository,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->segmentRepository = $segmentRepository;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        /** @var Segment $segment */
        foreach ($items as $segment) {
            $model = $this->segmentRepository->get($segment->getId());
            $this->loadedData[$segment->getId()] = $model->getData();
        }

        $data = $this->dataPersistor->get('amasty_segments_segment');

        if (!empty($data)) {
            $segment = $this->collection->getNewEmptyItem();
            $segment->setData($data);
            $this->loadedData[$segment->getId()] = $segment->getData();
            $this->dataPersistor->clear('amasty_segments_segment');
        }

        return $this->loadedData;
    }
}
