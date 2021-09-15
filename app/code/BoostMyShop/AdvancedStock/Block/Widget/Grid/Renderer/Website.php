<?php

namespace BoostMyShop\AdvancedStock\Block\Widget\Grid\Renderer;


class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_websiteFactory;
    protected $_websiteNameCache = [];

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        array $data = []
    ){
        parent::__construct($context, $data);

        $this->_websiteFactory = $websiteFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $html = [];
        foreach($row->getWebsiteIds() as $websiteId)
        {
            $html[] = $this->getWebsiteName($websiteId);
        }
        return implode('<br>', $html);
    }

    protected function getWebsiteName($websiteId)
    {
        if (!isset($this->_websiteNameCache[$websiteId]))
        {
            $this->_websiteNameCache[$websiteId] = $this->_websiteFactory->create()->load($websiteId)->getName();
        }
        return $this->_websiteNameCache[$websiteId];
    }


}