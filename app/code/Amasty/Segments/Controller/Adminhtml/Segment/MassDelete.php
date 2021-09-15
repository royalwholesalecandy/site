<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Controller\Adminhtml\Segment;

use Magento\Backend\App\Action;

class MassDelete extends \Amasty\Segments\Controller\Adminhtml\Segment
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Amasty\Segments\Model\ResourceModel\Segment\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassDelete constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Amasty\Segments\Api\SegmentRepositoryInterface $segmentRepository
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Amasty\Segments\Helper\Base $baseHelper
     * @param \Amasty\Segments\Model\Indexer\SegmentCustomerIndexer $segmentCustomerIndexer
     * @param \Amasty\Segments\Model\SalesRuleFactory $salesRuleFactory
     * @param \Amasty\Segments\Model\ResourceModel\Segment\CollectionFactory $collectionFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Segments\Api\SegmentRepositoryInterface $segmentRepository,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Amasty\Segments\Helper\Base $baseHelper,
        \Amasty\Segments\Model\Indexer\SegmentCustomerIndexer $segmentCustomerIndexer,
        \Amasty\Segments\Model\SalesRuleFactory $salesRuleFactory,
        \Amasty\Segments\Model\ResourceModel\Segment\CollectionFactory $collectionFactory,
        \Magento\Ui\Component\MassAction\Filter $filter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        parent::__construct(
            $context,
            $resultPageFactory,
            $coreRegistry,
            $segmentRepository,
            $resultLayoutFactory,
            $baseHelper,
            $segmentCustomerIndexer,
            $salesRuleFactory
        );
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $result = [];

            foreach ($collection as $segment) {
                $result[] = $segment->getSegmentId();
                $this->segmentRepository->delete($segment);
            }

            $this->messageManager->addSuccessMessage(
                __(sprintf('Segments with next ID\'s: %s, were deleted.', implode(', ', $result)))
            );

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while delete segment.')
            );
        }

        return $this->_redirect('amastysegments/*/');
    }
}
