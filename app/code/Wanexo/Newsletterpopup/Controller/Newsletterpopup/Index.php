<?php
namespace Wanexo\Newsletterpopup\Controller\Newsletterpopup;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Store\Model\ScopeInterface;


use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;


class Index extends Action
{
    
    
    protected $_error;
    
    protected $_errorMessage;
    
     protected $_successMessage;
    
    protected $_storeManager;


    protected $_customerSession;
   
    protected $_customerAccountManagement;
    
    public function __construct(
    Context $context,
    StoreManagerInterface $storeManager,
    Session $customerSession,
    CustomerAccountManagement $customerAccountManagement,
    SubscriberFactory $subscriberFactory,
    $error=false)
    {
        parent::__construct($context);
        $this->_error=$error;
        $this->_storeManager=$storeManager;
        $this->_customerSession=$customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerAccountManagement = $customerAccountManagement;
        $this-> _subscriberFactory=$subscriberFactory;
    }

    
    
    
   protected function validateEmailAvailable($email)
    {   
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
       
           
        if ($this->_customerSession->getCustomerDataObject()->getEmail() !== $email
            && !$this->customerAccountManagement->isEmailAvailable($email, $websiteId)
        ){
           
              $this->_error=true;
              $this->_errorMessage=__('This email address is already assigned to another user.');
            
        }
    }
     protected function validateGuestSubscription()
    {
        if ($this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
                ->getValue(
                    \Magento\Newsletter\Model\Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ) != 1
            && !$this->_customerSession->isLoggedIn()
        ) {
              $this->_error=true;
              $this->_errorMessage=__('This email address is already assigned to another user.');
            
        }
    }
     protected function validateEmailFormat($email)
    {
        
        
        if (!\Zend_Validate::is($email, 'EmailAddress')) {
           
            $this->_error=true;
            $this->_errorMessage=__('Please enter a valid email address.');
            
        }
    }

  
    
    public function execute()
    { 
       if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');
       }
        
        try {
            $this->validateEmailFormat($email);
            $this->validateGuestSubscription();
            $this->validateEmailAvailable($email);
            if(!$this->_error)
                {
                $status = $this->_subscriberFactory->create()->subscribe($email);
                 if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE)
                 {
                       $this->_error=false;
                       $this->_successMessage=__('The confirmation request has been sent.');
                       
                 } else
                 {    $this->_error=false;
                      $this->_successMessage=__('Thank you for your subscription.');
                 }
             }
          }
        catch (\Exception $e) {
                $this->_error=true;
                $this->_errorMessage=$e->getMessage();
               
            }
        
       $response = array('status' => ($this->_error === false?'success':'error'), 'msg' => ($this->_error === false ? $this->_successMessage:$this->_errorMessage));
		
		echo json_encode($response);
		exit;
    }
}
