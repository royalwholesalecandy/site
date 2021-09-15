<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\QWC;

use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\TypeQuery;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;

/**
 * Class Export
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\QWC
 */
class Export extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magenest\QuickBooksDesktop\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_configInterface;

    /**
     * @var \Magenest\QuickBooksDesktop\Helper\CreateQueue
     */
    protected $queueHelper;

    /**
     * Export constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magenest\QuickBooksDesktop\Model\Config $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configInterface
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magenest\QuickBooksDesktop\Model\Config $config,
        \Magenest\QuickBooksDesktop\Helper\CreateQueue $queueHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface
    ) {
        parent::__construct($context);
        $this->queueHelper = $queueHelper;
        $this->config = $config;
        $this->fileFactory = $fileFactory;
        $this->_configInterface = $configInterface;
    }

    /**
     * Export Varnish Configuration as .qwc
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $fileName = 'connect.qwc';
        $appName = 'Synchronization from Magento';

        $companyId = $this->queueHelper->getCompanyId();

        $checkType = $this->getRequest()->getParam('type');

        if ($checkType == TypeQuery::QUERY_COMPANY) {
            $fileName = 'company.qwc';
            $appName = 'Query Company';
        }

        if ($checkType == TypeQuery::QUERY_TAX) {
            $number = $this->_configInterface->getValue(
                'qbdesktop/qbd_setting/number_tax',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            ;
            $modelCheck = $this->_objectManager
                ->create('\Magenest\QuickBooksDesktop\Model\CustomQueue')->getCollection()
                ->addFieldToFilter('type', TypeQuery::QUERY_TAX)
                ->addFieldToFilter('company_id', $companyId);
            foreach ($modelCheck as $productQueue) {
                $productQueue->delete();
            }
            $maxRequest = 30;
            if ($number % $maxRequest > 0) {
                $check = intval($number / $maxRequest) + 1;
            } else {
                $check = intval($number / $maxRequest);
            }

            $model = $this->_objectManager
                ->create('\Magenest\QuickBooksDesktop\Model\CustomQueue')->getCollection()
                ->addFieldToFilter('type', TypeQuery::QUERY_TAX)
                ->addFieldToFilter('company_id', $companyId);
            if ($model->getSize() >= 1) {
                foreach ($model as $queue) {
                    $queue->setStatus(Status::STATUS_QUEUE);
                    $queue->save();
                }
            } else {
                for ($i = 1; $i <= $check; $i++) {
                    if ($i == 1) {
                        $operation = Operation::OPERATION_MOD; //start
                    } else {
                        $operation = Operation::OPERATION_ADD; //continue
                    }
                    $data = [
                        'ticket_id' => rand(0, 1000000),
                        'company_id' => $companyId,
                        'status' => Status::STATUS_QUEUE,
                        'type' => TypeQuery::QUERY_TAX,
                        'operation' => $operation
                    ];
                    $model = $this->_objectManager->create('\Magenest\QuickBooksDesktop\Model\CustomQueue');
                    $model->addData($data);
                    $model->save();
                }
            }
            $appName = 'Mapping Tax';
            $fileName = 'tax.qwc';
        }
		if ($checkType == TypeQuery::QUERY_INVENTORY) {
            $appName = 'Mapping Inventory';
            $fileName = 'inventory.qwc';
        }
		if ($checkType == TypeQuery::QUERY_ORDERPROCESSING) {
            $appName = 'Update Orders';
            $fileName = 'order.qwc';
        }
        $content = $this->config->getQWCFile($appName);
        return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
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
