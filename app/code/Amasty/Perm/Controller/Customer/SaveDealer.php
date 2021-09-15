<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Amasty\Perm\Model\DealerFactory;
use Amasty\Perm\Model\DealerCustomerFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Amasty\Perm\Helper\Data as PermHelper;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InputException;

class SaveDealer extends \Magento\Customer\Controller\AbstractAccount
{
    protected $_session;
    protected $_dealerFactory;
    protected $_formKeyValidator;
    protected $_permHelper;
    protected $_dealerCustomerFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        DealerFactory $dealerFactory,
        DealerCustomerFactory $dealerCustomerFactory,
        Validator $formKeyValidator,
        PermHelper $permHelper
    ){
        $this->_session = $customerSession;
        $this->_dealerFactory = $dealerFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_permHelper = $permHelper;
        $this->_dealerCustomerFactory = $dealerCustomerFactory;

        parent::__construct($context);
    }
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/edit');
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->_request->getParam('amasty_perm');

            if ($data && array_key_exists('dealer_id', $data) && $this->_permHelper->isInAccountMode()) {
                $customerId = $this->_session->getCustomerId();
                $dealer = $this->_dealerFactory->create()->load($data['dealer_id']);
                try {
                    if ($dealer->getId()){
                        $dealer->saveCustomers([$customerId], false);
                    } else {
                        $dealerCustomer = $this->_dealerCustomerFactory->create()->load($customerId, 'customer_id');
                        if ($dealerCustomer->getId()){
                            $dealerCustomer->delete();
                        }
                    }

                    $this->messageManager->addSuccess(__('You saved the dealer information.'));

                } catch (AuthenticationException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (InputException $e) {
                    $this->messageManager->addException($e, __('Invalid input'));
                } catch (\Exception $e) {
                    $message = __('We can\'t save the customer.')
                        . $e->getMessage()
                        . '<pre>' . $e->getTraceAsString() . '</pre>';
                    $this->messageManager->addException($e, $message);
                }
            }
        }

        return $resultRedirect->setPath('*/*/dealer');
    }
}