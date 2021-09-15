<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue;

use Magenest\QuickBooksDesktop\Model\Mapping;
use Magento\Backend\App\Action;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Priority;

/**
 * Class SyncCreditMemo
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue
 */
class SyncCreditMemo extends Action
{
    /**
     * @var QueueFactory
     */
    protected $_queueFactory;

    /**
     * @var CreateQueue
     */
    protected $_queueHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var Mapping
     */
    public $_map;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $memoCollection;

    /**
     * SyncCreditMemo constructor.
     *
     * @param Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param QueueFactory $queueFactory
     * @param CreateQueue $queueHelper
     * @param Mapping $map
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        QueueFactory $queueFactory,
        CreateQueue $queueHelper,
        Mapping $map
    ) {

        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_queueFactory = $queueFactory;
        $this->_queueHelper = $queueHelper;
        $this->_map = $map;
    }

    public function execute()
    {
        try {
            $companyId = $this->_queueHelper->getCompanyId();

            $mappingCollection = $this->_map->getCollection()
                ->addFieldToFilter('company_id', $companyId)
                ->addFieldToFilter('type', Type::QUEUE_CREDITMEMO)
                ->getColumnValues('entity_id');
            $allMemo = $this->getCollection()->getAllIds();
            $memoToQueueIds = array_diff($allMemo, $mappingCollection);

            $memoCollection = $this->getCollection()
                ->addFieldToFilter('entity_id', ['in' => $memoToQueueIds]);

            $totals = 0;

            foreach ($memoCollection as $memo) {
                $id = $memo->getId();
                $check = $this->_queueFactory->create()->getCollection()
                    ->addFieldToFilter('type', 'CreditMemo')
                    ->addFieldToFilter('entity_id', $id)
                    ->addFieldToFilter('company_id', $companyId)
                    ->addFieldToFilter('status', Status::STATUS_QUEUE);
                if ($check->count() == 0) {
                    $this->_queueHelper->createTransactionQueue($id, 'CreditMemo', Priority::PRIORITY_CREDITMEMO);
                    $totals++;
                }
            }
            $this->messageManager->addSuccessMessage(
                __(
                    sprintf('Totals %s Queue(s) have been created/updated', $totals)
                )
            );
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Order Collection
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCollection()
    {
        if (!$this->memoCollection) {
            $this->memoCollection = $this->_objectManager
                ->create('\Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection');
        }

        return $this->memoCollection;
    }

    /**
     * Always true
     *
     * @return bool
     */
    public function _isAllowed()
    {
        return true;
    }
}
