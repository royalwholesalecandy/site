<?php

namespace BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Overview;

class StockSettingsPerWebsite extends \Magento\Backend\Block\Template
{
    protected $_template = 'ErpProduct/Edit/Overview/StockSettingsPerWebsite.phtml';

    protected $_stockWebsiteCollectionFactory;
    protected $_config;
    protected $_backorderValues;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\StockWebsite\CollectionFactory $stockWebsiteCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\CatalogInventory\Model\Source\Backorders $backorderValues,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_stockWebsiteCollectionFactory = $stockWebsiteCollectionFactory;
        $this->_config = $config;
        $this->_backorderValues = $backorderValues;
    }

    public function getRecords()
    {
        return $this->_stockWebsiteCollectionFactory->create()->addProductFilter($this->getProduct()->getId());
    }

    public function getDefaultBackorderSetting()
    {
        $value = $this->_config->getMagentoBackorderSetting();
        return $value;
    }

    public function getDefaultBackorderSettingLabel()
    {
        $value = $this->_config->getMagentoBackorderSetting();
        $values = $this->_backorderValues->toOptionArray();
        foreach($values as $item)
        {
            if ($item['value'] == $value)
                return $item['label'];
        }

    }

    public function getBackorderValues()
    {
        return $this->_backorderValues->toOptionArray();
    }
}