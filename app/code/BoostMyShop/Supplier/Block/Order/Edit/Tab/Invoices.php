<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Invoices extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;

    protected $_invoiceCollectionFactory = null;
    protected $_typeList = null;
    protected $_invOrderCollectionFactory = null;
    protected $_supplierList = null;
    protected $_statusList = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

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
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \BoostMyShop\Supplier\Model\ResourceModel\Invoice\CollectionFactory $invoiceCollectionFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Invoice\Order\CollectionFactory $invOrderCollectionFactory,
        \BoostMyShop\Supplier\Model\Invoice\Type $typeList,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Collection $supplierList,
        \BoostMyShop\Supplier\Model\Invoice\Status $statusList,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->_invOrderCollectionFactory = $invOrderCollectionFactory;
        $this->_typeList = $typeList;
        $this->_supplierList = $supplierList;
        $this->_statusList = $statusList;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('orderInvoiceGrid');
        $this->setDefaultSort('bsi_id');
        $this->setDefaultDir('desc');
        $this->setTitle(__('Invoices'));
        $this->setUseAjax(true);
    }

    protected function getOrder()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->getOrder()->getInvoices();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn('bsi_created_at', ['align' => 'center', 'header' => __('Date'), 'index' => 'bsi_date', 'type' => 'date']);
        $this->addColumn('bsi_due_date', ['align' => 'center', 'header' => __('Due date'), 'index' => 'bsi_due_date', 'type' => 'date']);
        $this->addColumn('bsi_reference', ['align' => 'center', 'header' => __('Reference'), 'index' => 'bsi_reference', 'type' => 'text']);
        $this->addColumn('bsi_type', ['header' => __('Type'), 'index' => 'bsi_type', 'type' => 'options', 'options' => $this->_typeList->toOptionArray()]);
        $this->addColumn('bsi_sup_id', ['align' => 'center', 'header' => __('Supplier'), 'index' => 'bsi_sup_id', 'type' => 'options', 'options' => $this->_supplierList->toOptionArray()]);
        $this->addColumn('bsi_status', ['header' => __('Status'), 'index' => 'bsi_status', 'type' => 'options', 'options' => $this->_statusList->toOptionArray()]);
        $this->addColumn('bsi_total', ['align' => 'center', 'header' => __('Total'), 'index' => 'bsi_total', 'type' => 'currency']);
        $this->addColumn('bsi_total_paid', ['align' => 'center', 'header' => __('Total paid'), 'index' => 'bsi_total_paid', 'type' => 'currency']);
        $this->addColumn('bsio_total', ['align' => 'center', 'header' => __('Total Applied <br>to this PO'), 'index' => 'bsio_total', 'type' => 'currency']);
        
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/invoiceGrid', ['po_id' => $this->getOrder()->getId()]);
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('supplier/invoice/edit', ['bsi_id' => $item->getbsi_id()]);
    }

}
