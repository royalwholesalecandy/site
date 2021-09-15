<?php
namespace Wanexo\QuickView\Helper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_storeManager;
	protected $scopeConfig;
	protected $_scopeStore;
	
	public function __construct(
		Context $context,
        StoreManagerInterface $storeManagerInterface
	) {
		parent::__construct($context);
		$this->_scopeStore =  \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$this->_store = $storeManagerInterface;
	}
	
	public function getQuickUrl($_product){
		$productId = $_product->getId();
		return $this->getBaseUrl().'quickview/index/view/id/'.$productId;
	}
	
	public function getBaseUrl(){
		return $this->_store->getStore()->getBaseUrl();
	}
}