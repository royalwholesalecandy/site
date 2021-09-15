<?php
namespace Wanexo\Brand\Block\Adminhtml\Brand\Grid;

use Magento\Framework\Data\CollectionDataSourceInterface;
use Wanexo\Brand\Model\ResourceModel\Brand\Collection;
use Magento\Backend\Block\Widget\Grid\Column;

class DataSource implements CollectionDataSourceInterface
{
    
    public function filterStoreCondition(Collection $collection, Column $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $collection->addStoreFilter($value);
        return $this;
    }
}
