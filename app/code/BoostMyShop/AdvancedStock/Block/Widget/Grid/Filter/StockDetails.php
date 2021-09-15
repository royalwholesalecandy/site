<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BoostMyShop\AdvancedStock\Block\Widget\Grid\Filter;

/**
 * Select grid column filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class StockDetails extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{

    protected $_warehouseCollectionFactory;
    protected $_itemCollectionFactory;
    protected $_resource;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ){
        $this->_resourceHelper = $resourceHelper;
        $this->_resource = $resource;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        parent::__construct($context, $resourceHelper, $data);

    }

    /**
     * {@inheritdoc}
     */
    protected function _getOptions()
    {
        $options[] = ['value' => null, 'label' => ''];

        $collection = $this->_warehouseCollectionFactory->create()->setOrder('w_name', 'ASC')->addActiveFilter();
        foreach($collection as $item)
        {
            $options[] = ['value' => $item->getId(), 'label' => $item->getw_name()];
        }

        
        return $options;
    }

    

    /**
     * {@inheritdoc}
     */
    public function getHtml()
    {
        $html = '<select name="' . $this->_getHtmlName().'[warehouse]' . '" id="' . $this->_getHtmlId() .'[warehouse]'. '"' . $this->getUiId(
            'filter',
            $this->_getHtmlName().'[warehouse]'
        ) . 'class="no-changes admin__control-select">';
        $value = $this->getValue();

        foreach ($this->_getOptions() as $option) {
            if (is_array($value) && array_key_exists('warehouse', $value) && $value['warehouse'] == $option['value'])
                $selected = ' selected ';
            else
                $selected = '';
            $html .= '<option value="' . $option['value'] . '" ' . $selected . '>' . $option['label'] . '</option>';
        }

        $html .= '</select>';

        $html .= '<div class="range"><div class="range-line">' .
            '<input type="text" name="' .
            $this->_getHtmlName() .
            '[from]" id="' .
            $this->_getHtmlId() .
            '_from" placeholder="' .
            __(
                'From'
            ) . '" value="' . $this->getEscapedValue(
                'from'
            ) . '" class="input-text admin__control-text no-changes" ' . $this->getUiId(
                'filter',
                $this->_getHtmlName(),
                'from'
            ) . '/></div>';
        $html .= '<div class="range-line">' .
            '<input type="text" name="' .
            $this->_getHtmlName() .
            '[to]" id="' .
            $this->_getHtmlId() .
            '_to" placeholder="' .
            __(
                'To'
            ) . '" value="' . $this->getEscapedValue(
                'to'
            ) . '" class="input-text admin__control-text no-changes" ' . $this->getUiId(
                'filter',
                $this->_getHtmlName(),
                'to'
            ) . '/></div></div>';

        return $html;
    }


    /**
     * @param string|null $index
     * @return mixed
     */
    public function getValue($index = null)
    {
        if ($index) {
            return $this->getData('value', $index);
        }
        $value = $this->getData('value');
        if ((isset($value['from']) && strlen($value['from']) > 0) || (isset($value['to']) && strlen($value['to']) > 0) || (isset($value['warehouse']) && strlen($value['warehouse']) > 0)) {
            return $value;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCondition()
    {
        
        $productIds = array();

        $filterApplied = false;

        $value = $this->getValue();
       
        $warehouseId = 0;
        $minStockQty = -1;
        $maxStockQty = -1;

        if($value && is_array($value)){
          if(array_key_exists('warehouse', $value))
            $warehouseId = $value['warehouse'];

          if(array_key_exists('from', $value))
            $minStockQty = $value['from'];

          if(array_key_exists('to', $value))
            $maxStockQty = $value['to'];
        }

        if($warehouseId > 0 || $minStockQty > -1 || $maxStockQty > -1) {

            $connection  = $this->_resource->getConnection();
            $stockItemTable  = $connection->getTableName('bms_advancedstock_warehouse_item');
            $catalogtable  = $connection->getTableName('catalog_product_entity');
            $sql = 'SELECT tbl_stock_item.wi_product_id ';
            $sql .= 'FROM '.$stockItemTable.' tbl_stock_item ';
            $sql .= 'LEFT JOIN '.$catalogtable.' tbl_product ';
            $sql .= 'ON tbl_stock_item.wi_product_id = tbl_product.entity_id ';
            $sql .= 'WHERE (tbl_stock_item.wi_use_config_ideal_stock_level = 1) ';

            if($warehouseId > 0 ){
                $sql .= ' AND tbl_stock_item.wi_warehouse_id = '.$warehouseId;
            }

            if($minStockQty > -1){
                $sql .= ' AND tbl_stock_item.wi_physical_quantity >= '.$minStockQty;
            }
            if($maxStockQty > -1){
                $sql .= ' AND tbl_stock_item.wi_physical_quantity <= '.$maxStockQty;
            }

            $productIds = $this->_itemCollectionFactory->create()->getConnection()->fetchCol($sql);
            $filterApplied = true;
        }

        if(count($productIds) > 0 || $filterApplied)
            return array('in' => $productIds);
        else
            return null;
        
    }
}
