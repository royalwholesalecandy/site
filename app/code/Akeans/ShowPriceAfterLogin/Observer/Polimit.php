<?php
namespace Akeans\ShowPriceAfterLogin\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Framework\App\ResourceConnection;
use Akeans\ShowPriceAfterLogin\Helper\Data as akeanshelper;
class Polimit implements ObserverInterface
{
	protected $scopeConfig;
	protected $resource;
	protected $logger;
	protected $_request;
	protected $OrderidobjFactory;
	protected $_helperData;
	protected $_checkoutSession;
	public function __construct(
		\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\App\RequestInterface $request,
		ResourceConnection $resource,
		akeanshelper $helperData,
		\Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    	$this->resource = $resource;
    	$this->logger = $logger;
		$this->_request = $request;
        $this->scopeConfig = $scopeConfig;
		$this->_helperData = $helperData;
		$this->_checkoutSession = $_checkoutSession;
        //$this->UsedorderidobjFactory = $Usedorderidobj;
    }
    public function execute(Observer $observer)
    {
		$order = $observer->getEvent()->getOrder();
		$payment = $order->getPayment();
		$method = $payment->getMethodInstance();
		if($method->getCode() == 'purchaseorder'){

			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$customerSession = $objectManager->create('Magento\Customer\Model\Session');
			$customerId = $customerSession->getCustomer()->getId();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
			$websiteId = $storeManager->getWebsite()->getWebsiteId();
			$customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
			$customer = $customerFactory->load($customerId);
			$grandTotal = $order->getGrandTotal();
			$poCredit = (float)$customer->getCustomPoCredit();
			$totalDueAmount = $grandTotal + $poCredit;
			$customerObj = $objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface')->getById($order->getCustomerId());
			$customerObj->setWebsiteId($websiteId);
			$customerObj->setCustomAttribute('custom_po_credit', $totalDueAmount);
			$objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface')->save($customerObj);

		}
		
		//$customer->setCustomPoCredit($totalDueAmount)->save();
    }
	
}