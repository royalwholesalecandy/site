<?php
namespace Akeans\ShowPriceAfterLogin\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Framework\App\ResourceConnection;
use Akeans\ShowPriceAfterLogin\Helper\Data as akeanshelper;
class Cancheckout implements ObserverInterface
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
		$moduleName = $this->_request->getModuleName();
		$controllerName = $this->_request->getControllerName();
		$actionName = $this->_request->getActionName();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		
        if($moduleName == 'checkout' && $controllerName == 'index' && $actionName == 'index'){
			//$couponCode = $this->_helperData->getCouponCode();
			$quote = $this->_checkoutSession->getQuote();
			$configData = $this->_helperData->getConfigData();
			$this->doCacheClean($objectManager);
			$messageManage = $objectManager->create('\Magento\Framework\Message\ManagerInterface');
			if ($this->_helperData->isFreeShippingAvailable($quote)) {
				return;
			}

			if ($quote->getCouponCode() == $configData['config_showpriceafterlogin_coupon_codeval'] && $quote->getSubtotal() >= $configData['config_showpriceafterlogin_coupon_reqamount']) {
				return;
			}
			$minimumOrder = $configData['config_showpriceafterlogin_min_order_amount'];
		// 	$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/checkout.log');
		// $logger = new \Zend\Log\Logger();
		// $logger->addWriter($writer);
		// $logger->info($minimumOrder."===".$quote->getSubtotal()."===coming".$actionName);
			if ($quote->getSubtotal() < $minimumOrder) {
				
				$errorMessage = "Minimum order amount of $ $minimumOrder to order.";
				if ($quote->getCouponCode() == $configData['config_showpriceafterlogin_coupon_codeval']) {
					$errorMessage = "'" . $configData['config_showpriceafterlogin_coupon_codeval'] . "' coupon allows you to checkout for at least $" . $configData['config_showpriceafterlogin_coupon_reqamount'];
				}

				$messageManage->addError($errorMessage);
				// $responseFactory = $objectManager->create('\Magento\Framework\App\ResponseFactory');
				// $urlManager = $objectManager->create('\Magento\Framework\UrlInterface');
				// $redirectionUrl = $urlManager->getUrl('checkout/cart');
				// $responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
				
				$urlManager = $objectManager->create('\Magento\Framework\UrlInterface');
				$url = $urlManager->getUrl('checkout/cart/index');
				
				// below code redirects to cart controller
				$observer->getControllerAction()->getResponse()->setRedirect($url);

				return $this;
			}
			
			
		}
    	
    }
	public function doCacheClean($objectManager)
	{
		//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$_cacheTypeList = $objectManager->create('Magento\Framework\App\Cache\TypeListInterface');
		$_cacheFrontendPool = $objectManager->create('Magento\Framework\App\Cache\Frontend\Pool');
		$types = array('config','layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice');
		foreach ($types as $type) {
			$_cacheTypeList->cleanType($type);
		}
		foreach ($_cacheFrontendPool as $cacheFrontend) {
			$cacheFrontend->getBackend()->clean();
		}
	}
}
