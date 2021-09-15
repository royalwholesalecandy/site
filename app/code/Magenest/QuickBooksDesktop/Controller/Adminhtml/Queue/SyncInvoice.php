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
use Magenest\QuickBooksDesktop\Helper\CreateQueue;
use Magenest\QuickBooksDesktop\Model\QueueFactory as QueueFactory;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Priority;

/**
 * Class SyncInvoice
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue
 */
class SyncInvoice extends Action
{
    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * @var QueueFactory
     */
    protected $_queueFactory;

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
    protected $invoiceCollection;

    /**
     * SyncInvoice constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param QueueFactory $queueFactory
     * @param Mapping $map
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CreateQueue $queueHelper,
        QueueFactory $queueFactory,
        Mapping $map
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_queueHelper = $queueHelper;
        $this->_queueFactory = $queueFactory;
        $this->_map = $map;
    }

    public function execute()
    {
        try {
            $companyId = $this->_queueHelper->getCompanyId();

            $mappingCollection = $this->_map->getCollection()
                ->addFieldToFilter('company_id', $companyId)
                ->addFieldToFilter(
                    'type',
                    ["in" => [Type::QUEUE_INVOICE, Type::QUEUE_RECEIVEPAYMENT]]
                )
                ->getColumnValues('entity_id');
            $allInvoiceIds = $this->getCollection()->getAllIds();
            $invoiceIdToQueue = array_diff($allInvoiceIds, $mappingCollection);

            $invoiceCollection = $this->getCollection()
                ->addFieldToFilter('entity_id', ['in' => $invoiceIdToQueue]);
            $totals = 0;

            foreach ($invoiceCollection as $invoice) {
                $id = $invoice->getId();

                $check = $this->_queueFactory->create()->getCollection()//Open Invoice
                ->addFieldToFilter('type', 'Invoice')
                    ->addFieldToFilter('entity_id', $id)
                    ->addFieldToFilter('company_id', $companyId)
                    ->addFieldToFilter('status', Status::STATUS_QUEUE);
                if ($check->count() == 0) {
                    $this->_queueHelper->createTransactionQueue($id, 'Invoice', Priority::PRIORITY_INVOICE);
                    $totals++;
                }

                if ($invoice->getState() == 2) { // Paid Invoice
                    $check = $this->_queueFactory->create()->getCollection()
                        ->addFieldToFilter('type', 'ReceivePayment')
                        ->addFieldToFilter('entity_id', $id)
                        ->addFieldToFilter('company_id', $companyId)
                        ->addFieldToFilter('status', Status::STATUS_QUEUE);
                    if ($check->count() == 0) {
                        $this->_queueHelper->createTransactionQueue($id, 'ReceivePayment', Priority::PRIORITY_RECEIVEPAYMENT);
                        $totals++;
                    }
                }
            }
            $this->messageManager->addSuccessMessage(
                __(
                    sprintf('Totals %s Invoice Queue have been created/updated', $totals)
                )
            );
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        $this->_redirect('*/*/index');
    }


    /**
     * invoiceCollection Collection
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCollection()
    {
        if (!$this->invoiceCollection) {
            $this->invoiceCollection = $this->_objectManager
                ->create('\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection');
        }

        return $this->invoiceCollection;
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
