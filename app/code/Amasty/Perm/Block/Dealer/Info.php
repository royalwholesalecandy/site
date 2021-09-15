<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Block\Dealer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Info extends \Magento\Customer\Block\Account\Dashboard
{
    /** @var  \Amasty\Perm\Model\Dealer */
    protected $_dealer;

    /** @var \Amasty\Perm\Model\DealerCustomerFactory  */
    protected $_dealerCustomerFactory;

    /** @var \Magento\Widget\Model\Template\Filter  */
    protected $_templateFilter;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param \Amasty\Perm\Model\DealerCustomerFactory $dealerCustomerFactory
     * @param \Magento\Widget\Model\Template\Filter $templateFilter
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Amasty\Perm\Model\DealerCustomerFactory $dealerCustomerFactory,
        \Magento\Widget\Model\Template\Filter $templateFilter,
        array $data = []
    ) {
        $this->_dealerCustomerFactory = $dealerCustomerFactory;
        $this->_templateFilter = $templateFilter;
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
    }

    /**
     * @return \Amasty\Perm\Model\Dealer
     */
    public function getDealer()
    {
        if ($this->_dealer === null){
            $dealerCustomer = $this->_dealerCustomerFactory->create()
                ->load($this->getCustomer()->getId(), 'customer_id');

            $this->_dealer = $dealerCustomer->getDealer();
        }

        return $this->_dealer;
    }

    /**
     * Render cms-style urls
     *
     * @param string $html
     * @return string
     */
    protected function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);
        return $this->_templateFilter->filter($html);
    }
}