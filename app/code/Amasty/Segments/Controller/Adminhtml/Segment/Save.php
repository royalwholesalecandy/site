<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

namespace Amasty\Segments\Controller\Adminhtml\Segment;

use Magento\Backend\App\Action;
use Amasty\Segments\Model\ResourceModel\Segment;

class Save extends \Amasty\Segments\Controller\Adminhtml\Segment
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Amasty\Segments\Model\SegmentFactory
     */
    protected $segmentFactory;

    /**
     * @var \Amasty\Segments\Model\SegmentRepository
     */
    protected $segmentRepository;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Amasty\Segments\Api\SegmentRepositoryInterface $segmentRepository
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Amasty\Segments\Helper\Base $baseHelper
     * @param \Amasty\Segments\Model\Indexer\SegmentCustomerIndexer $segmentCustomerIndexer
     * @param \Amasty\Segments\Model\SalesRuleFactory $salesRuleFactory
     * @param \Amasty\Segments\Model\SegmentFactory $segmentFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
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
        \Amasty\Segments\Model\SegmentFactory $segmentFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
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
        $this->segmentFactory = $segmentFactory;
        $this->date = $date;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                /** @var Segment $model */
                $model = $this->segmentFactory->create();
                $segmentId = $this->getRequest()->getParam(self::SEGMENT_PARAM_URL_KEY);

                if ($segmentId) {
                    $model = $this->segmentRepository->get($segmentId);
                    if ($segmentId != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The wrong Segment is specified.')
                        );
                    }
                } else {
                    if (array_key_exists(self::SEGMENT_PARAM_URL_KEY, $data)) {
                        unset($data[self::SEGMENT_PARAM_URL_KEY]);
                    }
                }

                if (isset($data['rule']) && isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                    unset($data['rule']);
                    $salesRule = $this->salesRuleFactory->create();
                    $salesRule->loadPost($data);
                    $data['conditions_serialized'] = $salesRule->beforeSave()->getConditionsSerialized();
                    unset($data['conditions']);
                }

                $model->setData($data);
                $this->segmentRepository->save($model);
                $this->_getSession()->setPageData($model->getData());
                $this->messageManager->addSuccessMessage(__('You saved the Segment.'));
                $this->_getSession()->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect(
                        'amastysegments/*/edit',
                        [self::SEGMENT_PARAM_URL_KEY => $model->getSegmentId()]
                    );
                }

                return $this->_redirect('amastysegments/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('segment_id');

                if (!empty($id)) {
                    $this->_redirect('amastysegments/*/edit', [self::SEGMENT_PARAM_URL_KEY => $id]);
                } else {
                    $this->_redirect('amastysegments/*/edit');
                }

                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->_getSession()->setPageData($data);
                $this->_redirect(
                    'amastysegments/*/edit',
                    [self::SEGMENT_PARAM_URL_KEY => $this->getRequest()->getParam(self::SEGMENT_PARAM_URL_KEY)]
                );

                return;
            }
        }

        $this->_redirect('amastysegments/*/');
    }
}
