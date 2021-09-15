<?php

namespace BoostMyShop\Supplier\Block\Transit;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_transitCollection;
    protected $_supplierCollection;
    protected $_bmsHelper = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\Supplier\Model\ResourceModel\Transit\CollectionFactory $transitCollection,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\CollectionFactory $supplierCollection,
        \BoostMyShop\Supplier\Helper\Boostmyshop $bmsHelper,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_transitCollection = $transitCollection;
        $this->_supplierCollection = $supplierCollection;
        $this->_bmsHelper = $bmsHelper;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('transitGrid');
        $this->setDefaultSort('eta');
        $this->setDefaultDir('asc');
        $this->setTitle(__('Products in transit'));
        $this->setUseAjax(true);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_transitCollection->create()->init();
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn('entity_id', ['header' => __('ID'), 'index' => 'entity_id', 'type' => 'number']);
        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'renderer' => 'BoostMyShop\Supplier\Block\Transit\Renderer\Sku']);
        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);
        $this->addColumn('qty_to_receive', ['header' => __('Qty to receive'), 'index' => 'qty_to_receive', 'type' => 'number']);
        $this->addColumn('eta', ['header' => __('Estimated time of arrival'), 'index' => 'eta', 'type' => 'date', 'filter' => false]);
        $this->addColumn('related_po', ['header' => __('Related PO'), 'index' => 'entity_id', 'renderer' => 'BoostMyShop\Supplier\Block\Transit\Renderer\Po', 'filter' => 'BoostMyShop\Supplier\Block\Transit\Filter\Supplier', 'sortable' => false]);
        if($this->_bmsHelper->advancedStockModuleIsInstalled())
            $this->addColumn('stock_details', ['header' => __('Stock details'), 'filter_index' => 'entity_id', 'sortable' => false, 'index' => 'entity_id', 'align' => 'left', 'renderer' => 'BoostMyShop\Supplier\Block\Transit\Renderer\StockDetails','filter' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Filter\StockDetails']);
        else
            $this->addColumn('stock_details', ['header' => __('Stock details'), 'filter' => false, 'sortable' => false, 'index' => 'entity_id', 'align' => 'left', 'renderer' => 'BoostMyShop\Supplier\Block\Transit\Renderer\StockDetails']);

        $this->_eventManager->dispatch('bms_supplier_transit_grid_prepare_columns', ['grid' => $this]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/Grid');
    }


}
