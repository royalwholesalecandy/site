<?php
namespace Magenest\QuickBooksDesktop\Observer\Adminhtml\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magento\Customer\Model\CustomerFactory;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;
class Update implements ObserverInterface
{
	/**
	 * @var QueueFactory
	 */
	protected $_queueFactory;

	/**
	 * @var CustomerFactory
	 */
	protected $_customerFactory;

	/**
	 * @var Mapping
	 */
	protected $_map;

	protected $_queueHelper;

	/**
	 * Update constructor.
	 * @param QueueFactory $queueFactory
	 * @param CustomerFactory $customerFactory
	 * @param Mapping $map
	 */
	function __construct(
		QueueFactory $queueFactory,
		CustomerFactory $customerFactory,
		Mapping $map,
		CreateQueue $_queueHelper
	) {
		$this->_queueHelper = $_queueHelper;
		$this->_customerFactory = $customerFactory;
		$this->_queueFactory = $queueFactory;
		$this->_map = $map;
	}

	/**
	 * Admin edit information
	 *
	 * @param Observer $observer
	 */
	function execute(Observer $observer) {
		/**
		 * 2019-12-13 Dmitry Fedyuk https://github.com/mage2pro
		 * «Undefined index: REQUEST_URI
		 * in app/code/Magenest/QuickBooksDesktop/Observer/Adminhtml/Customer/Update.php on line 70»:
		 * https://github.com/royalwholesalecandy/core/issues/26
		 * @see \Magenest\QuickBooksDesktop\Observer\Customer\Address::execute()
		 * @see \Magenest\QuickBooksDesktop\Observer\Customer\Edit::execute()
		 */
		if (!df_url_path_contains('qbdesktop/customer/')) {
			$event = $observer->getEvent();
			/** @var \Magento\Framework\Event\Observer $customer */
			$customer = $event->getCustomer();
			$customerId = $customer->getId();
			$companyId = $this->_queueHelper->getCompanyId();

			$qbId = $this->_map->getCollection()
				->addFieldToFilter('company_id', $companyId)
				->addFieldToFilter('type', Type::QUEUE_CUSTOMER)
				->addFieldToFilter('entity_id', $customerId)
				->getFirstItem()->getData();

			$action = $qbId ? 'Mod' : 'Add';
			$operation = $qbId ? Operation::OPERATION_MOD : Operation::OPERATION_ADD;
			$this->_queueHelper->createCustomerQueue($customerId, $action, $operation);
		}
	}
}
