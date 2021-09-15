<?php

namespace BoostMyShop\AdvancedStock\Model\Message;

class ConfigurationVerification implements \Magento\Framework\Notification\MessageInterface
{

    protected $urlBuilder;
    protected $config;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \BoostMyShop\AdvancedStock\Model\Config $config
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
    }

    public function isDisplayed()
    {
        return (count($this->getIssues()) > 0);
    }

    public function getIdentity()
    {
        return md5('ConfigurationVerification');
    }

    public function getText()
    {
        $url = $this->urlBuilder->getUrl('indexer/indexer/list');

        $txt = __('Some configuration settings are not correct for the ERP Inventory extension : ');
        $txt .= '<ul>';
        foreach($this->getIssues() as $issue)
        {
            $txt .= '<li> - '.$issue.'</li>';
        }
        $txt .= '</ul>';

        return $txt;

    }

    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }

    public function getIssues()
    {
        $issues = [];

        //if ($this->config->getDecreaseStockWhenOrderIsPlaced())
        //    $issues[] = 'Stores > Configuration > Catalog > Inventory > Stock Options > Decrease Stock When Order is Placed must be set to No.';

        if (!$this->config->canBackInStock())
            $issues[] = 'Stores > Configuration > Catalog > Inventory > Stock Options > Set Items Status to be In Stock When Order is Cancelled should be set to Yes to put products back in stock.';


        return $issues;
    }

}
