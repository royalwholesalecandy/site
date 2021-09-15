<?php


namespace Metagento\Referrerurl\Observer\Frontend;


class SalesOrderPlaceAfter implements
    \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute( \Magento\Framework\Event\Observer $observer )
    {
        if ( $this->scopeConfig->getValue('referrerurl/general/track_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ) {
            $order = $observer['order'];
            if ( array_key_exists('referrer_url', $_COOKIE) ) {
                $referrerUrl = $_COOKIE['referrer_url'];
                $order->setData('referrer_url', $referrerUrl);
                $order->save();
            }
        }
        return;
    }
}