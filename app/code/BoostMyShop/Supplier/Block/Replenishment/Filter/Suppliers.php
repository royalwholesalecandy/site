<?php

namespace BoostMyShop\Supplier\Block\Replenishment\Filter;

class Suppliers extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    protected $_suppliersFactory;
    protected $_supplierProductFactory;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Framework\DB\Helper $resourceHelper,
                                \BoostMyShop\Supplier\Model\ResourceModel\Supplier\CollectionFactory $suppliersFactory,
                                \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Product\CollectionFactory $supplierProductFactory,
                                array $data = [])
    {
        parent::__construct($context, $resourceHelper, $data);

        $this->_suppliersFactory = $suppliersFactory;
        $this->_supplierProductFactory = $supplierProductFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    protected function _getOptions()
    {
        $result = [];
        $result[] = ['value' => '', 'label' => ''];
        foreach ($this->_suppliersFactory->create()->setOrder('sup_name', 'ASC') as $supplier) {
            $result[] = ['value' => $supplier->getId(), 'label' => $supplier->getsup_name()];
        }

        return $result;
    }

    protected function getSupplierValue()
    {
        $values = $this->getValue();
        if (is_array($values) && isset($values['supplier']))
            return $values['supplier'];
    }

    protected function getSupplierSkuValue()
    {
        $values = $this->getValue();
        if (is_array($values) && isset($values['supplier_sku']))
            return $values['supplier_sku'];
    }

    public function getHtml()
    {
        $html = '<select name="' . $this->_getHtmlName() . '[supplier]" id="' . $this->_getHtmlId() . '"' . $this->getUiId(
                'filter',
                $this->_getHtmlName()
            ) . 'class="no-changes admin__control-select">';
        $value = $this->getSupplierValue();
        $html .= '<option value=""> </option>';
        foreach ($this->_getOptions() as $option) {
            if (is_array($option['value'])) {
                $html .= '<optgroup label="' . $this->escapeHtml($option['label']) . '">';
                foreach ($option['value'] as $subOption) {
                    $html .= $this->_renderOption($subOption, $value);
                }
                $html .= '</optgroup>';
            } else {
                $html .= $this->_renderOption($option, $value);
            }
        }
        $html .= '</select>';

        $html .= '<br>'.__('Supplier Sku').'<br>';
        $html .= '<input type="text" name="' . $this->_getHtmlName() . '[supplier_sku]" id="' . $this->_getHtmlId() . '" value="'.$this->getSupplierSkuValue().'">';

        return $html;
    }


    /**
     * Get condition
     *
     * @return array|null
     */
    public function getCondition()
    {
        if ($this->getSupplierValue() || $this->getSupplierSkuValue())
        {

            $productIds = $this->_supplierProductFactory->create()->getProductIdsForSupplier($this->getSupplierValue(), $this->getSupplierSkuValue());
            return ['in' => $productIds];
        }

        return null;

    }
}
