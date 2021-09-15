<?php

namespace BoostMyShop\AdvancedStock\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;

class Stocks extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'stocks';


    protected $_warehouseItemCollectionFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {


        if (isset($dataSource['data']['items'])) {
            foreach($dataSource['data']['items'] as &$item)
            {
                $productId = $item['entity_id'];
                $html = [];
                $collection = $this->_warehouseItemCollectionFactory->create()->addProductFilter($productId)->joinWarehouse();
                foreach($collection as $wiItem)
                {
                    if (!$wiItem->getwi_available_quantity() && !$wiItem->getwi_physical_quantity())
                        continue;

                    $color = ($wiItem->getwi_available_quantity() > 0 ? 'green' : 'red');
                    $available = ($wiItem->getwi_available_quantity() > 0 ? $wiItem->getwi_available_quantity() : '0');
                    $physical = ($wiItem->getwi_physical_quantity() > 0 ? $wiItem->getwi_physical_quantity() : '0');
                    $html[] = '<font color="'.$color.'">'.$wiItem->getw_name().': '.$available.'/'.$physical.'</font>';
                }
                $item['stocks'] = implode('<br>', $html);
            }
        }

        return $dataSource;
    }
}
