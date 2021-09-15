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
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;

/**
 * Class SyncCustomer
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue
 */
class SyncCustomer extends Action
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
    protected $customerCollection;

    /**
     * SyncCustomer constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param QueueFactory $queueFactory
     * @param Mapping $map
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        QueueFactory $queueFactory,
        CreateQueue $_queueHelper,
        Mapping $map
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_queueFactory = $queueFactory;
        $this->_queueHelper = $_queueHelper;
        $this->_map = $map;
    }

    /**
     * sync customer to queue table
     */
    public function execute()
    {
        try {
            $companyId = $this->_queueHelper->getCompanyId();

            $mappingCollection = $this->_map->getCollection()
                ->addFieldToFilter('company_id', $companyId)
                ->addFieldToFilter('type', Type::QUEUE_CUSTOMER)
                ->getColumnValues('entity_id');

            $allCustomerIds = $this->getCollection()->getAllIds();
			echo count($allCustomerIds);die;
            $customerIdToQueue = array_diff($allCustomerIds, $mappingCollection);
            $customerCollection = $this->getCollection()
                ->addFieldToFilter('entity_id', ['in' => $customerIdToQueue]);
            $totals = 0;

            foreach ($customerCollection as $customer) {
                $id = $customer->getId();
                $check = $this->_queueFactory->create()->getCollection()
                    ->addFieldToFilter('type', 'Customer')
                    ->addFieldToFilter('entity_id', $id)
                    ->addFieldToFilter('company_id', $companyId)
                    ->addFieldToFilter('status', Status::STATUS_QUEUE);
                if ($check->count() == 0) {
                    $this->_queueHelper->createCustomerQueue($id, "Add", Operation::OPERATION_ADD);
                    $totals++;
                }
            }

            $this->messageManager->addSuccessMessage(
                __(
                    sprintf('Totals %s Customer Queue have been created/updated', $totals)
                )
            );
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Psr\Log\LoggerInterface')
            ->info("Loop1");

        $this->_redirect('*/*/index');
    }

    /**
     * Customer Collection
     *
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCollection()
    {
        if (!$this->customerCollection) {
            $this->customerCollection = $this->_objectManager
                ->create('\Magento\Customer\Model\ResourceModel\Customer\Collection');
        }

        return $this->customerCollection;
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
