<?php


namespace Metagento\Referrerurl\Observer\Frontend;


class CustomerSaveCommitAfter implements
    \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->customerResource = $customerResource;
        $this->scopeConfig      = $scopeConfig;
    }


    public function execute( \Magento\Framework\Event\Observer $observer )
    {
        if ( $this->scopeConfig->getValue('referrerurl/general/track_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ) {
            $customer = $observer['customer'];
            if ( array_key_exists('referrer_url', $_COOKIE) ) {
                $referrerUrl = $_COOKIE['referrer_url'];
                $customer->setData('referrer_url', $referrerUrl);
                $this->customerResource->saveAttribute($customer, 'referrer_url');
            }
            return;
        }
    }
}