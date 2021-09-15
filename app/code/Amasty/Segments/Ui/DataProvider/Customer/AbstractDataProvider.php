<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Ui\DataProvider\Customer;

use \Amasty\Segments\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use \Amasty\Segments\Model\ResourceModel\Guest\CollectionFactory as GuestCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Amasty\Segments\Controller\Adminhtml\Segment;

/**
 * Class ReviewDataProvider
 *
 * @method CustomerCollectionFactory|GuestCollectionFactory getCollection
 */
class AbstractDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CustomerCollectionFactory|GuestCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Amasty\Segments\Model\SegmentRepository
     */
    protected $segmentRepository;

    /**
     * @var SearchCriteria
     */
    protected $searchCriteria;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        $collectionFactory,
        RequestInterface $request,
        \Amasty\Segments\Model\SegmentRepository $segmentRepository,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        $this->request = $request;
        $this->segmentRepository = $segmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $currentSegmentId = $this->request->getParam(Segment::SEGMENT_PARAM_URL_KEY, 0);

        $this->getCollection()->setCurrentSegment(
            $this->segmentRepository->get($currentSegmentId)
        );

        $this->getCollection()->getCommonFilters()->loadFromIndex();

        $arrItems = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => $this->getCollection()->getData(),
        ];

        return $arrItems;
    }

    /**
     * Returns search criteria
     *
     * @return \Magento\Framework\Api\Search\SearchCriteria
     */
    public function getSearchCriteria()
    {
        if (!$this->searchCriteria) {
            $this->searchCriteria = $this->searchCriteriaBuilder->create();
            $this->searchCriteria->setRequestName($this->name);
        }
        return $this->searchCriteria;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        return $dataSource;
    }
}

