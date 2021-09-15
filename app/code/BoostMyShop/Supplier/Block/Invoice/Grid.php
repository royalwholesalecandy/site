<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace BoostMyShop\Supplier\Block\Invoice;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    
    protected $_invoiceCollectionFactory;
    protected $_resource;
    protected $_supplierList = null;
    protected $_statusList = null;
    protected $_typeList = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\Supplier\Model\ResourceModel\Invoice\CollectionFactory $invoiceCollectionFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Collection $supplierList,
        \BoostMyShop\Supplier\Model\Invoice\Status $statusList,
        \BoostMyShop\Supplier\Model\Invoice\Type $typeList,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->_supplierList = $supplierList;
        $this->_statusList = $statusList;
        $this->_typeList = $typeList;
        $this->_resource = $resource;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('invoiceOrderGrid');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }


    protected function _prepareCollection()
    {
        $collection = $this->_invoiceCollectionFactory->create();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        
        $this->addColumn('bsi_id',['align' => 'center','header' => __('Invoice id'),'index' => 'bsi_id']);
        $this->addColumn('bsi_date', ['align' => 'center', 'header' => __('Date'), 'index' => 'bsi_date', 'type' => 'date']);
        $this->addColumn('bsi_due_date', ['align' => 'center', 'header' => __('Due date'), 'index' => 'bsi_due_date', 'type' => 'date']);
        $this->addColumn('bsi_reference', ['align' => 'center', 'header' => __('Reference'), 'index' => 'bsi_reference', 'type' => 'text']);
        $this->addColumn('bsi_type', ['header' => __('Type'), 'index' => 'bsi_type', 'type' => 'options', 'options' => $this->_typeList->toOptionArray()]);
        $this->addColumn('bsi_sup_id', ['align' => 'center', 'header' => __('Supplier'), 'index' => 'bsi_sup_id', 'type' => 'options', 'options' => $this->_supplierList->toOptionArray()]);
        $this->addColumn('bsi_status', ['header' => __('Status'), 'index' => 'bsi_status', 'type' => 'options', 'options' => $this->_statusList->toOptionArray()]);
        $this->addColumn('bsi_total', ['align' => 'center', 'header' => __('Total'), 'index' => 'bsi_total', 'type' => 'currency']);
        $this->addColumn('bsi_total_paid', ['align' => 'center', 'header' => __('Balance due'), 'index' => 'bsi_total_paid', 'type' => 'number', 'renderer' => '\BoostMyShop\Supplier\Block\Invoice\Grid\Renderer\BalanceDue', 'filter_condition_callback' => [$this, 'filterBalanceDue']]);
        $this->addColumn('bsi_total_applied', ['align' => 'center', 'header' => __('Balance to apply'), 'index' => 'bsi_total_applied', 'type' => 'number', 'renderer' => '\BoostMyShop\Supplier\Block\Invoice\Grid\Renderer\BalanceToApply', 'filter_condition_callback' => [$this, 'filterBalanceToApply']]);
        $this->addColumn('related_orders', ['align' => 'center', 'header' => __('Related orders'), 'index' => 'bsi_total_applied', 'filter' => false, 'sortable' => false, 'type' => 'renderer', 'renderer' => '\BoostMyShop\Supplier\Block\Invoice\Grid\Renderer\RelatedOrders']);
       
        $this->_eventManager->dispatch('bms_supplier_invoice_grid', ['grid' => $this]);

        $this->addExportType('supplier/invoice/exportCsv', __('CSV'));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/invoiceGrid', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getRowUrl($item)
    {
        return $this->getUrl('supplier/invoice/edit', ['bsi_id' => $item->getbsi_id()]);
    }

    public function filterBalanceToApply($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) { 
            return;
        }

        $value = $column->getFilter()->getValue();
        if(isset($value['from']) && $value['from'] != '' && isset($value['to']) && $value['to'] != ''){
            $from = $value['from'];
            $to = $value['to'];
            $collection->getSelect()->where("main_table.bsi_total-main_table.bsi_total_applied >='".$from."' and main_table.bsi_total-main_table.bsi_total_applied <='".$to."'");
        } elseif(isset($value['from']) && $value['from'] != ''){
            $from = $value['from'];
            $collection->getSelect()->where("main_table.bsi_total-main_table.bsi_total_applied >='".$from."'");
        } elseif(isset($value['to']) && $value['to'] != ''){
            $to = $value['to'];
            $collection->getSelect()->where("main_table.bsi_total-main_table.bsi_total_applied <='".$to."'");
        } 

        return $this;
    }

    public function filterBalanceDue($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) { 
            return;
        }

        $value = $column->getFilter()->getValue();
        if(isset($value['from']) && $value['from'] != '' && isset($value['to']) && $value['to'] != ''){
            $from = $value['from'];
            $to = $value['to'];
            $collection->getSelect()->where("main_table.bsi_total-main_table.bsi_total_paid >='".$from."' and main_table.bsi_total-main_table.bsi_total_paid <='".$to."'");
        } elseif(isset($value['from']) && $value['from'] != ''){
            $from = $value['from'];
            $collection->getSelect()->where("main_table.bsi_total-main_table.bsi_total_paid >='".$from."'");
        } elseif(isset($value['to']) && $value['to'] != ''){
            $to = $value['to'];
            $collection->getSelect()->where("main_table.bsi_total-main_table.bsi_total_paid <='".$to."'");
        } 
    }
    
}
