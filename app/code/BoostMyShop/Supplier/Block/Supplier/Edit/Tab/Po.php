<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Po extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;

    protected $_posFactory = null;

    protected $_poStatuses = null;

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
        \BoostMyShop\Supplier\Model\ResourceModel\Order\CollectionFactory $posFactory,
        \BoostMyShop\Supplier\Model\Order\Status $poStatuses,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_posFactory = $posFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_poStatuses = $poStatuses;
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
        $this->setId('posGrid');
        $this->setDefaultSort('po_id');
        $this->setDefaultDir('desc');
        $this->setTitle(__('Purchase Orders'));
        $this->setUseAjax(true);
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'assigned_user_role') {
            $userRoles = $this->getSelectedRoles();
            if (empty($userRoles)) {
                $userRoles = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('role_id', ['in' => $userRoles]);
            } else {
                if ($userRoles) {
                    $this->getCollection()->addFieldToFilter('role_id', ['nin' => $userRoles]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function getSupplier()
    {
        return $this->_coreRegistry->registry('current_supplier');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_posFactory->create();
        $collection->addSupplierFilter($this->getSupplier()->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn('po_reference', ['header' => __('Reference'), 'index' => 'po_reference']);
        $this->addColumn('po_eta', ['header' => __('Estimated date of arrival'), 'index' => 'po_eta', 'type' => 'timestamp']);
        $this->addColumn('po_status', ['header' => __('Status'), 'index' => 'po_status', 'type' => 'options', 'options' => $this->_poStatuses->toOptionArray()]);
        $this->addColumn('po_grandtotal', ['header' => __('Total'), 'index' => 'po_status', 'type' => 'currency', 'options' => $this->_poStatuses->toOptionArray()]);
        $this->addColumn('po_delivery_progress', ['header' => __('Delivery progress'), 'index' => 'po_delivery_progress']);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/posGrid', ['sup_id' => $this->getSupplier()->getId()]);
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('supplier/order/edit', ['po_id' => $item->getId()]);
    }
}
