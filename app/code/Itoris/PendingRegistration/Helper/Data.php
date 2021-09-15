<?php
namespace Itoris\PendingRegistration\Helper;
use Magento\Framework\App\Action\Action;
class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	private static $_canSendItorisEmail = false;
	private $request;
	protected $_objectManager;
	protected $messageManager;
	protected $_request;
	protected $_cacheManager;
	protected $_pendingTemplatePath = null;

	/** @var \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig */
	protected $_scopeConfig = null;

	private $componentAlias = 'pending_registration';

	public $templatesTableName = 'itoris_pendingregistration_templates';
	public $usersTableName = 'itoris_pendingregistration_users';
	public $settingsTableName = 'itoris_pendingregistration_settings';
	public $customerGroupsTableName = 'itoris_pendingregistration_customergroups';
	public $configTableName = 'core_config_data';

	const IPR_EMAIL_REG_TO_ADMIN = 1;
	const IPR_EMAIL_REG_TO_USER = 2;
	const IPR_EMAIL_APPROVED = 3;
	const IPR_EMAIL_DECLINED = 4;

	const XML_PATH_DEFAULT_USER_TEMPLATE = 'itoris_pendingreg/events/user_template';
	const XML_PATH_DEFAULT_APPROVED_TEMPLATE = 'itoris_pendingreg/events/approved_template';
	const XML_PATH_DEFAULT_DECLINED_TEMPLATE = 'itoris_pendingreg/events/declined_template';
	const XML_PATH_DEFAULT_ADMIN_TEMPLATE = 'itoris_pendingreg/events/admin_template';

	const XML_PATH_MODULE_ENABLED = 'itoris_pendingreg/general/enabled';
	const XML_PATH_ALL_USER_STATUS = 'itoris_pendingreg/existing_users/all_users_status';
	const XML_PATH_CUSTOMER_GROUPS = 'itoris_pendingreg/general/customer_groups';

	const XML_PATH_GENERAL_EMAIL = 'trans_email/ident_general/email';
	const XML_PATH_CORE = 'itoris_core/installed/';
	const XML_PATH_DISABLE_MODULES = 'advanced/modules_disable_output/';

	const XML_PATH_SENDER_TO_ADMIN = 'itoris_pendingreg/events/admin_itoris_identity';
	const XML_PATH_SENDER_TO_NEW_USER = 'itoris_pendingreg/events/new_itoris_identity';
	const XML_PATH_SENDER_TO_APPROVED = 'itoris_pendingreg/events/approved_itoris_identity';
	const XML_PATH_SENDER_TO_DECLINED = 'itoris_pendingreg/events/declined_itoris_identity';

	const MODULE_NAME = 'Itoris_PendingRegistration';

	public $sender;

	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\ObjectManagerInterface $objectManager
	 * @param \Magento\Framework\Message\ManagerInterface $messageManager
	 * @param \Magento\Framework\App\CacheInterface $cacheInterface
	 */
	function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\CacheInterface $cacheInterface
	){
		parent::__construct($context);
		$this->_cacheManager = $cacheInterface;
		$this->_request = $context->getRequest();
		$this->messageManager = $messageManager;
		$this->_objectManager = $objectManager;
		$db = $this->getResourceConnection();
		$this->templatesTableName = $db->getTableName($this->templatesTableName);
		$this->usersTableName = $db->getTableName($this->usersTableName);
		$this->settingsTableName = $db->getTableName($this->settingsTableName);
		/** @var \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig */
		$this->_scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
	}

	function init($request){
		// save request, necessary for check registration
		$this->request = $request;

		// init constants
		if (!defined('IPR_EMAIL_REG_TO_ADMIN')) {
			define('IPR_EMAIL_REG_TO_ADMIN', 1);
			define('IPR_EMAIL_REG_TO_USER', 2);
			define('IPR_EMAIL_APPROVED', 3);
			define('IPR_EMAIL_DECLINED', 4);
		}

	}

	function customerLogout($controller){
		/** @var \Magento\Customer\Model\Session $session */
		$session = $this->_objectManager->get('Magento\Customer\Model\Session');
		$session->logout()->setBeforeAuthUrl($this->_getUrl('*/*/login'));
		$this->getResponseInterface()->setRedirect($this->_getUrl('*/*/login'));
		$this->getResponseInterface()->sendHeaders();
		$this->safeExit($controller);
	}
	function customerCreate($controller){
		/** @var \Magento\Customer\Model\Session $session */
		$session = $this->_objectManager->get('Magento\Customer\Model\Session');
		$session->logout()->setBeforeAuthUrl($this->_getUrl('*/*/create'));
		$this->getResponseInterface()->setRedirect($this->_getUrl('*/*/create'));
		$this->getResponseInterface()->sendHeaders();
		$this->safeExit($controller);
	}
	function isCanSendEmail( $templateType, \Itoris\PendingRegistration\Model\Scope $scope = null ){
		/** @var \Itoris\PendingRegistration\Model\Template $template */
		$template = $this->_objectManager->create( 'Itoris\PendingRegistration\Model\Template' );
		$template->load( $templateType, 'type', $scope );
		return $template->isActive() && $template->getEmailContent()!='' && $template->getFromName()!='' && $template->getFromEmail()!='' /*&& $template->getSubject()!=''*/;
	}

	function getGroups(){
		$scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$storeCode = $this->getStoreManager()->getStore()->getCode();
		$groups = $this->_scopeConfig->getValue(self::XML_PATH_CUSTOMER_GROUPS, $scope, $storeCode);
		if($groups != null) $groups = explode(',',$groups);
		return $groups;
	}

	function isEnabled($storeViewId = 0){
		$scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$storeCode = $this->getStoreManager()->getStore()->getCode();
		if ($storeViewId) $storeCode = $this->getStoreManager()->getStore($storeViewId)->getCode();
		$isEnabled = (int) $this->_scopeConfig->getValue(self::XML_PATH_MODULE_ENABLED, $scope, $storeCode);
		$registerData = $this->_scopeConfig->getValue(self::XML_PATH_CORE.self::MODULE_NAME, $scope, $storeCode);

		$isRegister = count(explode('|', $registerData));
		return $isEnabled == 1 && $isRegister == 2;
	}

	/**
	 * @param string $text
	 * @return bool
	 */
	function isRepeatMessage($text){
		$messageIsset = false;
		$messages = $this->messageManager->getMessages()->getItems();
		foreach($messages as $key=>$value){
			if($value->getText() == $text) $messageIsset = true;
		}
		return $messageIsset;
	}
	/**
	 * @return \Magento\Framework\DataObject\Copy\Config
	 */
	function getConfig(){
		return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\DataObject\Copy\Config');
	}

	function sendEmail($templateType, \Magento\Customer\Model\Customer $user, \Itoris\PendingRegistration\Model\Scope $scope = null) {

		if($templateType == self::IPR_EMAIL_REG_TO_ADMIN){

			$this->_pendingTemplatePath = self::XML_PATH_DEFAULT_ADMIN_TEMPLATE;
			$this->sender = self::XML_PATH_SENDER_TO_ADMIN;

		}elseif($templateType == self::IPR_EMAIL_REG_TO_USER){

			$this->_pendingTemplatePath = self::XML_PATH_DEFAULT_USER_TEMPLATE;
			$this->sender = self::XML_PATH_SENDER_TO_NEW_USER;

		}elseif($templateType == self::IPR_EMAIL_APPROVED){

			$this->_pendingTemplatePath = self::XML_PATH_DEFAULT_APPROVED_TEMPLATE;
			$this->sender = self::XML_PATH_SENDER_TO_NEW_USER;

		}elseif($templateType == self::IPR_EMAIL_DECLINED){
			$this->_pendingTemplatePath = self::XML_PATH_DEFAULT_DECLINED_TEMPLATE;
			$this->sender = self::XML_PATH_SENDER_TO_DECLINED;
		}
		$emailVars = [
			'customer' => $user,
		];
		$emailOptions = [
			'area' => $this->getArea($this->getTemplateId($user)),
			'store' => $user->getData('store_id')
		];

		/** @var \Magento\Framework\Mail\Template\TransportBuilder $_transport */
		$_transport = $this->_objectManager->get('Magento\Framework\Mail\Template\TransportBuilder');

		if ($this->_pendingTemplatePath != self::XML_PATH_DEFAULT_ADMIN_TEMPLATE) {
			$email = $user->getEmail();
		} else {
			$email = $this->_scopeConfig->getValue(self::XML_PATH_GENERAL_EMAIL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $user->getData('store_id'));
		}
		if($this->getTemplateId($user) != 'disable'){
			try {
				$transport = $_transport->setTemplateIdentifier($this->getTemplateId($user))
					->setFrom($this->_scopeConfig->getValue($this->sender, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $user->getData('store_id')))
					->setTemplateOptions($emailOptions)
					->setTemplateVars($emailVars)
					->addTo($email)
					->getTransport();
				$transport->sendMessage();
			} catch (\Exception $e) {
			}
		}
	}

	/**
	 * @param \Magento\Customer\Model\Customer $user
	 * @return string
	 */
	protected function getTemplateId(\Magento\Customer\Model\Customer $user){
		return $this->_scopeConfig->getValue($this->_pendingTemplatePath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $user->getData('store_id'));
	}

	/**
	 * @param string $templateId
	 * @return string
	 */
	protected function getArea($templateId){
		if(is_numeric($templateId)){
			return \Magento\Framework\App\Area::AREA_FRONTEND;
		} else{
			return \Magento\Framework\App\Area::AREA_ADMINHTML;
		}
	}

	protected function _prepareRegFieldsOptions($options) {
		$addressTypes = ['', 's_'];
		foreach ($addressTypes as $prefix) {
			if (isset($options[$prefix . 'country_id'])) {
				/** @var \Magento\Directory\Model\Country $country */
				$country = $this->_objectManager->create('Magento\Directory\Model\Country ')->loadByCode($options[$prefix . 'country_id']);
				$options[$prefix . 'country_id'] = $country->getName();
				if (isset($options[$prefix . 'region_id'])) {
					$region = $country->getRegionCollection()->getItemById($options[$prefix . 'region_id']);
					if ($region) {
						$options[$prefix . 'region_id'] = $region->getName();
					}
				}
			}
		}

		if (isset($options['group_id'])) {
			/** @var \Magento\Customer\Model\ResourceModel\Group\Collection $sourceModel */
			$sourceModel = $this->_objectManager->create('Magento\Customer\Model\ResourceModel\Group\Collection');
			$groups = $sourceModel->toOptionArray();
			foreach ($groups as $group) {
				if ($group['value'] == $options['group_id']) {
					$options['group_id'] = $group['label'];
				}
			}
		}

		return $options;
	}
	/**
	 * @return \Magento\Framework\App\Response\Http
	 */
	protected function getResponse(){
		return $this->_objectManager->create('Magento\Framework\App\Response\Http');
	}

	/**
	 * @return \Magento\Framework\App\ResponseInterface
	 */
	protected function getResponseInterface(){
		return $this->_objectManager->get('Magento\Framework\App\ResponseInterface');
	}
	/**
	 * Create connection adapter instance
	 * @return \Magento\Framework\App\ResourceConnection
	 */
	protected function getResourceConnection(){
		return $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
	}
	/*
	function tryRegister()
	{
		if($this->_request->isPost() && $this->_request->getPost('registration', null) == 'true'){
			$sn = $this->_request->getPost('sn', null);
			if($sn == null){
				$this->messageManager->addError(__('Invalid serial number.'));
				return false;
			}

			$sn = trim($sn);
			try{
				$response = \Itoris\Installer\Client::registerCurrentStoreHost($this->componentAlias, $sn);
				if($response == 0){
					$this->messageManager->addSuccess(__('The component has been registered!'));
					$this->_cacheManager->clean();
				}else{
					$this->messageManager->addError(__('Invalid serial number!'));
				}
			}catch(\Exception $e){
				$this->messageManager->addError($e->getMessage());
			}

		}
	}
	*/
	/**
	 * @return \Itoris\RegFields\Helper\Data
	 */
	function getRegFieldsHelper(){
		return $this->_objectManager->create('Itoris\RegFields\Helper\Data');
	}
	/**
	 * @return \Itoris\PendingRegistration\Model\Scope
	 */
	function getFrontendScope(){
		/** @var $scope \Itoris\PendingRegistration\Model\Scope */
		$scope = $this->_objectManager->create('Itoris\PendingRegistration\Model\Scope');
		$scope->setWebsiteId($this->getStoreManager()->getWebsite()->getId());
		$scope->setStoreId($this->getStoreManager()->getStore()->getId());
		return $scope;
	}

	/**
	 * @param \Magento\Customer\Model\Customer $customer
	 * @return \Itoris\PendingRegistration\Model\Scope
	 */
	function getCustomerScope(\Magento\Customer\Model\Customer $customer){
		/** @var $scope \Itoris\PendingRegistration\Model\Scope */
		$scope = $this->_objectManager->create('Itoris\PendingRegistration\Model\Scope');
		$scope->setStoreId($customer->getStoreId());
		$scope->setWebsiteId($customer->getWebsiteId());
		return $scope;
	}

	/**
	 * Check if Registration Fields Manager is installed and active
	 *
	 * @return bool
	 */
	function isRegFieldsActive() {
		return (bool)$this->getModuleManager()->isEnabled('Itoris_RegFields');
	}
	function isPendingsActive() {
		return (bool)$this->getModuleManager()->isEnabled('Itoris_PendingRegistration');
	}

	function isStoreLoginControlRequireLogin() {
		$isEnabled = $this->getModuleManager()->isEnabled('Itoris_StoreLoginControl');
		if ((bool)$isEnabled) {
			/** @var $helper \Itoris\StoreLoginControl\Helper\Data */
			$helper = $this->_objectManager->create('Itoris\StoreLoginControl\Helper\Data');
			return $helper->isMustLogin();
		}

		return false;
	}

	/**
	 * @return \Itoris\PendingRegistration\Helper\Data
	 */
	public static function inst() {
		return \Magento\Framework\App\ObjectManager::getInstance()->create('\Itoris\PendingRegistration\Helper\Data');
	}

	/**
	 * @return \Magento\Store\Model\StoreManagerInterface
	 */
	function getStoreManager(){
		return $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
	}

	/**
	 * @return \Magento\Framework\Module\Manager
	 */
	function getModuleManager(){
		return $this->_objectManager->create('Magento\Framework\Module\Manager');
	}

	/**
	 * @return bool
	 */
	function getCanSendItorisEmail(){
		return self::$_canSendItorisEmail;
	}

	function setCanSendItorisEmail($bool){
		self::$_canSendItorisEmail = $bool;
	}

	/**
	 * 2019-12-13 Dmitry Fedyuk https://github.com/mage2pro
	 * @used-by customerLogout()
	 * @used-by customerCreate()
	 * @used-by \Itoris\PendingRegistration\Observers\Login\CheckLoginAbility::execute()
	 * @used-by \Itoris\PendingRegistration\Observers\Register::createCustomer()
	 * @param Action|null $c [optional]
	 */
	function safeExit(Action $c = null) {
		/**
		 * 2019-12-13 Dmitry Fedyuk https://github.com/mage2pro
		 * «Call to a member function getActionFlag() on null
		 * in app/code/Itoris/PendingRegistration/Helper/Data.php:407:
		 * https://github.com/royalwholesalecandy/core/issues/22
		 */
		if ($c) {
			$c->getActionFlag()->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
		}
	}
}