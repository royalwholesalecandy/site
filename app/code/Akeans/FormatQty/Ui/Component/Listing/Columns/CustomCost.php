<?php
namespace Akeans\FormatQty\Ui\Component\Listing\Columns;

class CustomCost extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.custom_cost_price';
    
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        
        
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    //$item[$fieldName] = (int)$item[$fieldName];
                    $item[$fieldName] = $priceHelper->currency($item[$fieldName], true, false);
                }
            }
        }
        
        return $dataSource;
    }
}
?>
