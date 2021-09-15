<?php

namespace BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Overview;

class SalesHistory extends \Magento\Backend\Block\Template
{
    protected $_template = 'ErpProduct/Edit/Overview/SalesHistory.phtml';

    protected $_salesHistoryCollectionFactory;
    protected $_config;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\SalesHistory\CollectionFactory $salesHistoryCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_salesHistoryCollectionFactory = $salesHistoryCollectionFactory;
        $this->_config = $config;
    }

    public function getHistoryAsJson()
    {
        $data = ['sh_range_1' => 0, 'sh_range_2' => 0, 'sh_range_3' => 0];

        $collection = $this->_salesHistoryCollectionFactory->create()->addProductFilter($this->getProduct()->getId());
        foreach($collection as $item)
        {

            $data['sh_range_1'] += (int)$item->getData('sh_range_1');
            $data['sh_range_2'] += (int)$item->getData('sh_range_2');
            $data['sh_range_3'] += (int)$item->getData('sh_range_3');

        }

        $newData = [];
        $newData[] = $data['sh_range_1'];
        $newData[] = $data['sh_range_2'];
        $newData[] = $data['sh_range_3'];

        return json_encode($newData);
    }

    public function getCategoriesAsJson()
    {
        $cat = [];
        $cat[] = $this->_config->getSetting('stock_level/history_range_1').' last weeks';
        $cat[] = $this->_config->getSetting('stock_level/history_range_2').' last weeks';
        $cat[] = $this->_config->getSetting('stock_level/history_range_3').' last weeks';
        return json_encode($cat);
    }

    public function getHistoryRecords()
    {
        $data = ['sh_range_1' => 0, 'sh_range_2' => 0, 'sh_range_3' => 0];

        $collection = $this->_salesHistoryCollectionFactory->create()->addProductFilter($this->getProduct()->getId());
        foreach($collection as $item)
        {

            $data['sh_range_1'] += (int)$item->getData('sh_range_1');
            $data['sh_range_2'] += (int)$item->getData('sh_range_2');
            $data['sh_range_3'] += (int)$item->getData('sh_range_3');
        }

        $newData = [];

        $newData['last '.$this->_config->getSetting('stock_level/history_range_1').' weeks'] = $data['sh_range_1'];
        $newData['last '.$this->_config->getSetting('stock_level/history_range_2').' weeks'] = $data['sh_range_2'];
        $newData['last '.$this->_config->getSetting('stock_level/history_range_3').' weeks'] = $data['sh_range_3'];

        return $newData;
    }

}