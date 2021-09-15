<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Ui\DataProvider\Customer;

use Magento\Framework\App\RequestInterface;
use Amasty\Segments\Model\ResourceModel\Guest\CollectionFactory;
use Amasty\Segments\Model\ResourceModel\Guest\Collection;
use Amasty\Segments\Controller\Adminhtml\Segment;

/**
 * Class GuestDataProvider
 *
 * @method Collection getCollection
 */
class GuestDataProvider extends \Amasty\Segments\Ui\DataProvider\Customer\AbstractDataProvider
{
    /**
     * GuestDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param \Amasty\Segments\Model\SegmentRepository $segmentRepository
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Amasty\Segments\Model\ResourceModel\Guest\CollectionFactory $collectionFactory,
        RequestInterface $request,
        \Amasty\Segments\Model\SegmentRepository $segmentRepository,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $request,
            $segmentRepository,
            $searchCriteriaBuilder,
            $meta,
            $data
        );
    }
}

