<?php

namespace BoostMyShop\Supplier\Block\Invoice\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class PaymentGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $_coreRegistry = null;
    protected $_typeList = null;
    protected $_invPaymentsCollectionFactory = null;
    protected $_supplierList = null;

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
        \BoostMyShop\Supplier\Model\ResourceModel\Invoice\Payments\CollectionFactory $invPaymentsCollectionFactory,
        \BoostMyShop\Supplier\Model\Invoice\Type $typeList,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Collection $supplierList,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_invPaymentsCollectionFactory = $invPaymentsCollectionFactory;
        $this->_typeList = $typeList;
        $this->_supplierList = $supplierList;
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
        $this->setId('invoicePaymentGrid');
        $this->setDefaultSort('bsi_id');
        $this->setDefaultDir('desc');
        $this->setTitle(__('Payments'));
        $this->setUseAjax(true);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    protected function getInvoice()
    {
        return $this->_coreRegistry->registry('current_supplier_invoice');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_invPaymentsCollectionFactory->create();
        $collection->addInvoiceFilter($this->getInvoice()->getId());
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn('bsip_date', ['header' => __('Date'),'filter' => false, 'sortable' => false, 'index' => 'bsip_date', 'type' => 'date']);
        $this->addColumn('bsip_method', ['header' => __('Method'), 'index' => 'bsip_method','filter' => false, 'sortable' => false, 'type' => 'text']);
        $this->addColumn('bsip_total', ['header' => __('Total'), 'filter' => false, 'sortable' => false,'index' => 'bsip_total', 'type' => 'currency']);
        $this->addColumn('bsip_notes', ['header' => __('Notes'),'filter' => false, 'sortable' => false, 'index' => 'bsip_notes', 'type' => 'text']);
        $this->addColumn('bsip_remove', ['header' => __('Remove'), 'filter' => false, 'sortable' => false, 'index' => 'bsip_id', 'renderer' => '\BoostMyShop\Supplier\Block\Invoice\Edit\Tab\Payments\Renderer\Remove', 'align' => 'center']);
        
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/invoiceGrid', ['po_id' => $this->getInvoice()->getId()]);
    }

}
