<?php

namespace BoostMyShop\AdvancedStock\Block\Widget\Grid\Renderer;


class StockHistory extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_config = null;
    protected $_warehouseItemCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\SalesHistory\CollectionFactory $salesHistoryCollectionFactory,
        array $data = []
    ){
        parent::__construct($context, $data);

        $this->_config = $config;
        $this->_salesHistoryCollectionFactory = $salesHistoryCollectionFactory;
    }

    public function render(\Magento\Framework\DataObject $product)
    {
        $html = '<table border="1">';

        $records = $this->getHistoryRecords($product->getId());

        $html .= '<tr>';
        foreach($records as $k => $v)
        {
            $html .= '<th style="text-align: center;">'.$k.'</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        foreach($records as $k => $v)
        {
            $html .= '<td>'.$v.'</td>';
        }
        $html .= '</tr>';

        $html .= '</table>';

        return $html;
    }


    public function getHistoryRecords($productId)
    {
        $data = ['sh_range_1' => 0, 'sh_range_2' => 0, 'sh_range_3' => 0];

        $collection = $this->_salesHistoryCollectionFactory->create()->addProductFilter($productId);
        foreach($collection as $item)
        {
            $data['sh_range_1'] += (int)$item->getData('sh_range_1');
            $data['sh_range_2'] += (int)$item->getData('sh_range_2');
            $data['sh_range_3'] += (int)$item->getData('sh_range_3');
        }

        $newData = [];

        $newData[$this->_config->getSetting('stock_level/history_range_1').' weeks'] = $data['sh_range_1'];
        $newData[$this->_config->getSetting('stock_level/history_range_2').' weeks'] = $data['sh_range_2'];
        $newData[$this->_config->getSetting('stock_level/history_range_3').' weeks'] = $data['sh_range_3'];

        return $newData;
    }

}