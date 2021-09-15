<?php
/**
 * Created by PhpStorm.
 * User: Nguyen Thanh Nam
 * Date: 7/24/2018
 * Time: 2:46 PM
 */

namespace Magenest\QuickBooksDesktop\Helper;

use Magenest\QuickBooksDesktop\Model\CompanyFactory;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Priority;

class CreateQueue
{
    /**
     * @var QueueFactory
     */
    protected $_queueFactory;

    /**
     * @var CompanyFactory
     */
    protected $_companyFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Generate constructor.
     * @param TicketFactory $ticketFactory
     * @param DateTime $date
     */
    public function __construct(
        CompanyFactory $companyFactory,
        ScopeConfigInterface $scopeConfig,
        QueueFactory $queueFactory
    ) {
        $this->_companyFactory = $companyFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_queueFactory = $queueFactory;
    }

    public function getCompanyId()
    {
        $company = $this->_companyFactory->create();
        $company->load(1, 'status');
        return $company->getCompanyId();
    }

    public function getQuickBooksVersion()
    {
        $version = $this->scopeConfig->getValue(
            'qbdesktop/qbd_setting/quickbook_version',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $version;
    }

    public function createItemInventoryAddQueue($productId)
    {
        $this->createQueue(
            $productId,
            'ItemInventoryAdd',
            'Product',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_PRODUCT
        );
    }

    public function createCustomerQueue($customerId, $action, $operation)
    {
        $this->createQueue(
            $customerId,
            'Customer' . $action,
            'Customer',
            $operation,
            Priority::PRIORITY_CUSTOMER
        );
    }

    public function createGuestQueue($customerId, $action, $operation)
    {
        $this->createQueue(
            $customerId,
            'Customer' . $action,
            'Guest',
            $operation,
            Priority::PRIORITY_GUEST
        );
    }

    public function createItemInventoryModQueue($productId)
    {
        $this->createQueue(
            $productId,
            'ItemInventoryMod',
            'Product',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_PRODUCT
        );
    }

    public function createItemNonInventoryAddQueue($productId)
    {
        $this->createQueue(
            $productId,
            'ItemNonInventoryAdd',
            'Product',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_PRODUCT
        );
    }

    public function createItemNonInventoryModQueue($productId)
    {
        $this->createQueue(
            $productId,
            'ItemNonInventoryMod',
            'Product',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_PRODUCT
        );
    }

    public function createSalesOrderQueue($orderId)
    {
        $this->createQueue(
            $orderId,
            'SalesOrderAdd',
            'SalesOrder',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_SALESORDER
        );
    }

    public function createCreditMemoQueue($creditMemoId)
    {
        $this->createQueue(
            $creditMemoId,
            'CreditMemoAdd',
            'CreditMemo',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_CREDITMEMO
        );
    }

    public function createOpenInvoiceQueue($invoiceId)
    {
        $this->createQueue(
            $invoiceId,
            'InvoiceAdd',
            'Invoice',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_INVOICE
        );
    }

    public function createPaidInvoiceQueue($invoiceId)
    {
        $this->createQueue(
            $invoiceId,
            'ReceivePaymentAdd',
            'ReceivePayment',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_RECEIVEPAYMENT
        );
    }

    public function createQueue($entityId, $actionName, $type, $operation, $priority)
    {
        try {
            $info = [
                'action_name' => $actionName,
                'enqueue_datetime' => time(),
                'type' => $type,
                'status' => Status::STATUS_QUEUE,
                'entity_id' => $entityId,
                'operation' => $operation,
                'company_id' => $this->getCompanyId(),
                'priority' => $priority
            ];
            $model = $this->_queueFactory->create();
            $modelCheck = $model->getCollection()
                ->addFieldToFilter('type', $type)
                ->addFieldToFilter('entity_id', $entityId)
                ->addFieldToFilter('company_id', $this->getCompanyId())
                ->addFieldToFilter('status', Status::STATUS_QUEUE)->getLastItem();
            $modelCheck->addData($info);
            $modelCheck->save();
        } catch (\Exception $exception) {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Psr\Log\LoggerInterface')
                ->info($exception->getMessage());
        }
    }

    public function createTransactionQueue($entityId, $type, $priority)
    {
        try {
            $info = [
                'action_name' => $type . 'Add',
                'enqueue_datetime' => time(),
                'dequeue_datetime' => '',
                'type' => $type,
                'status' => Status::STATUS_QUEUE,
                'company_id' => $this->getCompanyId(),
                'entity_id' => $entityId,
                'operation' => Operation::OPERATION_ADD,
                'priority' => $priority
            ];
            $model = $this->_queueFactory->create();
            $modelCheck = $model->getCollection()
                ->addFieldToFilter('type', $type)
                ->addFieldToFilter('entity_id', $entityId)
                ->addFieldToFilter('company_id', $this->getCompanyId())
                ->addFieldToFilter('status', Status::STATUS_QUEUE)->getLastItem();
            $modelCheck->addData($info);
            $modelCheck->save();
        } catch (\Exception $exception) {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Psr\Log\LoggerInterface')
                ->info($exception->getMessage());
        }
    }
}
