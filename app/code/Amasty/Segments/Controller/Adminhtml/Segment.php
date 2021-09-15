<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Segment extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Segments::segments';

    /**
     * id param name in url
     */
    const SEGMENT_PARAM_URL_KEY = 'segment_id';

    /**
     * id param name in url
     */
    const CONDITION_PARAM_URL_KEY = 'id';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Amasty\Segments\Model\SegmentRepository
     */
    protected $segmentRepository;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Amasty\Segments\Helper\Base
     */
    protected $baseHelper;

    /**
     * @var \Amasty\Segments\Model\Indexer\SegmentCustomerIndexer
     */
    protected $segmentCustomerIndexer;

    /**
     * @var \Amasty\Segments\Model\SalesRuleFactory
     */
    protected $salesRuleFactory;

    /**
     * Segment constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Amasty\Segments\Api\SegmentRepositoryInterface $segmentRepository
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Amasty\Segments\Helper\Base $baseHelper
     * @param \Amasty\Segments\Model\Indexer\SegmentCustomerIndexer $segmentCustomerIndexer
     * @param \Amasty\Segments\Model\SalesRuleFactory $salesRuleFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Segments\Api\SegmentRepositoryInterface $segmentRepository,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Amasty\Segments\Helper\Base $baseHelper,
        \Amasty\Segments\Model\Indexer\SegmentCustomerIndexer $segmentCustomerIndexer,
        \Amasty\Segments\Model\SalesRuleFactory $salesRuleFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->segmentRepository = $segmentRepository;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->baseHelper = $baseHelper;
        $this->segmentCustomerIndexer = $segmentCustomerIndexer;
        $this->salesRuleFactory = $salesRuleFactory;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(self::ADMIN_RESOURCE);
        $this->_addBreadcrumb(__('Manage Segments'), __('Manage Segments'));

        return $this;
    }

    /**
     * @param $segment
     * @return mixed
     */
    protected function initCurrentSegment($segment)
    {
        $this->baseHelper->initCurrentSegment($segment);

        return $segment;
    }
}

