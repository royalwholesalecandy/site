<?php

namespace BoostMyShop\Margin\Block\Cogs;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_cogsCollectionFactory;
    protected $_attributesetCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\Margin\Model\ResourceModel\Cogs\CollectionFactory $cogsCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributesetCollectionFactory,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_cogsCollectionFactory = $cogsCollectionFactory;
        $this->_attributesetCollectionFactory = $attributesetCollectionFactory;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('cogsGrid');
        $this->setSaveParametersInSession(true);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_cogsCollectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('order_date', ['header' => __('Date'), 'type' => 'datetime', 'index' => 'order_date']);
        $this->addColumn('order_id', ['header' => __('Order #'), 'index' => 'increment_id']);
        $this->addColumn('bill_to', ['header' => __('Bill to'), 'index' => 'billing_name']);
        $this->addColumn('store_id', ['header' => __('Store'), 'index' => 'order_store_id', 'type' => 'store', 'renderer' => 'BoostMyShop\Margin\Block\Widget\Grid\Column\Renderer\Cogs\Store']);
        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku']);
        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);
        $this->addColumn('attributeset', ['header' => __('Attribute Set'), 'index' => 'attribute_set_id', 'type' => 'options', 'options' => $this->getAttributeSetOptions()]);
        $this->addColumn('qty_invoiced', ['header' => __('Qty invoiced'), 'index' => 'qty_invoiced', 'type' => 'number']);
        $this->addColumn('base_cost', ['header' => __('Cost'), 'type' => 'number', 'renderer' => 'BoostMyShop\Margin\Block\Widget\Grid\Column\Renderer\Cogs\Cost', 'index' => 'base_cost', 'filter' => false, 'sortable' => false]);
        $this->addColumn('base_price', ['header' => __('Price'), 'type' => 'number', 'index' => 'base_price']);
        $this->addColumn('row_invoiced', ['header' => __('Total invoiced'), 'type' => 'number', 'index' => 'row_invoiced']);
        $this->addColumn('margin_value', ['header' => __('Margin value'), 'renderer' => 'BoostMyShop\Margin\Block\Widget\Grid\Column\Renderer\Cogs\MarginValue', 'type' => 'number', 'filter' => false, 'sortable' => false]);
        $this->addColumn('margin_percent', ['header' => __('Margin %'), 'renderer' => 'BoostMyShop\Margin\Block\Widget\Grid\Column\Renderer\Cogs\MarginPercent', 'type' => 'number', 'filter' => false, 'sortable' => false]);

        $this->addExportType('*/*/exportCsv', __('CSV'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return "";
    }

    protected function getAttributeSetOptions()
    {
        $options = [];

        $collection = $this->_attributesetCollectionFactory->create()->setEntityTypeFilter(4);  //todo : use constant instead
        foreach($collection as $item)
        {
            $options[$item->getId()] = $item->getattribute_set_name();
        }

        return $options;
    }

    public function addExportType($url, $label)
    {
        $this->_exportTypes[] = new \Magento\Framework\DataObject(
            ['url' => $this->getUrl($url), 'label' => $label]
        );
        return $this;
    }
    
}
