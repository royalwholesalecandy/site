<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerSaveAfterObserver implements ObserverInterface
{
    /** @var \Amasty\Perm\Helper\Data  */
    protected $_permHelper;

    /** @var \Magento\Framework\App\Request\Http  */
    protected $_request;

    /** @var \Amasty\Perm\Model\DealerFactory  */
    protected $_dealerFactory;

    /**
     * @param \Amasty\Perm\Helper\Data $permHelper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Amasty\Perm\Model\DealerFactory $dealerFactory
     */
    public function __construct(
        \Amasty\Perm\Helper\Data $permHelper,
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Perm\Model\DealerFactory $dealerFactory
    ){
        $this->_permHelper = $permHelper;
        $this->_request = $request;
        $this->_dealerFactory = $dealerFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $dealerId = null;

        $amastyPermData = $this->_request->getParam('amasty_perm'); //customer registration form frontend
        $customerData = $this->_request->getParam('customer'); //customer edit on backend

        if ($amastyPermData && array_key_exists('dealer_id', $amastyPermData)){
            $dealerId = $amastyPermData['dealer_id'];
        } else if ($customerData && array_key_exists('amasty_perm_dealer', $customerData)){
            $dealerId = $customerData['amasty_perm_dealer'];
        } else if ($this->_permHelper->isBackendDealer()){
            $dealerId = $this->_permHelper->getBackendDealer()->getId();
        }

        if ($dealerId !== null){

            $customer = $observer->getEvent()->getData('data_object');

            if (!$customer) {
                $customer = $observer->getEvent()->getData('customer_data_object');
            }

            $dealer = $this->_dealerFactory->create()->load($dealerId);
            if ($dealer->getId()) {
                $dealer->saveCustomers([$customer->getId()], false);
            }
        }
    }
}
