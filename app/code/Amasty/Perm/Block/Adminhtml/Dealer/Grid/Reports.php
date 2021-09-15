<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Block\Adminhtml\Dealer\Grid;

use Magento\Backend\Block\Widget\Grid\Column;

class Reports extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;

    protected $_jsonEncoder;

    protected $_collectionFactory;

    protected $_dealerFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Perm\Model\DealerFactory $dealerFactory,
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory,
        array $data = []
    ){

        parent::__construct($context, $backendHelper, $data);
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        $this->_dealerFactory = $dealerFactory;

    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('dealerReportsGrid');
        $this->setUseAjax(true);
    }

    protected function getNewFields()
    {
        $fields = array(
            'base_grand_total',

            'base_discount_amount',
            'base_discount_canceled',
            'base_discount_invoiced',
            'base_discount_refunded',

            'base_shipping_amount',
            'base_shipping_canceled',
            'base_shipping_invoiced',
            'base_shipping_refunded',

            'base_shipping_tax_amount',
            'base_shipping_tax_refunded',

            'base_subtotal',
            'base_subtotal_canceled',
            'base_subtotal_invoiced',
            'base_subtotal_refunded',

            'base_tax_amount',
            'base_tax_canceled',
            'base_tax_invoiced',
            'base_tax_refunded',
        );
        return $fields;
    }

    protected function _prepareCollection()
    {
        $orders =  $this->_collectionFactory->getReport('sales_order_grid_data_source');


        $select = $orders->getSelect();
        $select->reset(\Zend_Db_Select::COLUMNS);

        $fields = array('increment_id', 'created_at', 'billing_name', 'shipping_name', 'store_id', 'status', 'entity_id');
        $map    = array();
        foreach ($fields as $f)
            $map['m_'.$f] = 'main_table.' . $f;
        $select->from(null, $map);

        //$select->from(null, array());
        $fields = array();
        foreach ($this->getNewFields() as $f)
            $fields['o_'.$f] = 'o.' . $f;

        $select->joinInner( array('o'=>$orders->getTable('sales_order')),
            'o.entity_id=main_table.entity_id',
            $fields);

        $select->joinInner( array('dealer_order'=>$orders->getTable('amasty_perm_dealer_order')),
            'dealer_order.order_id=main_table.entity_id and dealer_order.dealer_id = '.intval($this->getDealer()->getId()),
            $fields);

        $this->setCollection($orders);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('m_increment_id', array(
            'header'       => __('Order #'),
            'width'        => '100',
            'index'        => 'm_increment_id',
            'filter_index' => 'main_table.increment_id',
        ));

        $this->addColumn('m_created_at', array(
            'header'       => __('Purchase On'),
            'index'        => 'm_created_at',
            'type'         => 'm_datetime',
            'filter_index' => 'main_table.created_at',
        ));

        $this->addColumn('m_billing_name', array(
            'header'       => __('Bill to Name'),
            'index'        => 'm_billing_name',
            'filter_index' => 'main_table.billing_name',
        ));

        $this->addColumn('m_shipping_name', array(
            'header'       => __('Shipped to Name'),
            'index'        => 'm_shipping_name',
            'filter_index' => 'main_table.billing_name',
        ));

        foreach ($this->getNewFields() as $f) {
            $this->addColumn('o_' . $f, array(
                'header'       => __(ucwords(str_replace('_',' ', $f))),
                'index'        => 'o_' . $f,
                'type'         => 'currency',
                'currency'     => 'order_currency_code',
                'filter_index' => 'o.' . $f,
            ));
        }

        if ($this->_storeManager->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'       => __('Bought From'),
                'index'        => 'm_store_id',
                'filter_index' => 'main_table.store_id',
                'type'         => 'store',
                'store_view'   => true
            ));
        }

        $this->addColumn('status', array(
            'header'       => __('Status'),
            'index'        => 'm_status',
            'filter_index' => 'main_table.status',
            'type'         => 'options',
            'width'        => '70px',
//            'options'      => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('action', array(
            'header'    => __('Action'),
            'index'     => 'm_entity_id',
            'type'      => 'action',
            'filter'    => false,
            'sortable'  => false,
            'actions'   => array(
                array(
                    'caption' => __('View Order'),
                    'url'     => array('base'=>'sales/order/view'),
                    'field'   => 'order_id'
                ),
            )
        ));

        $this->addExportType('amasty_perm/dealer/reportsExportCsv', __('CSV'));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('amasty_perm/dealer/editrolereportsgrid', ['user_id' => $this->getDealer()->getUserId()]);
    }

    public function getDealer()
    {
        $dealer = parent::getDealer();
        if ($dealer === null){
            $userId = $this->getRequest()->getParam('user_id');
            $dealer = $this->_dealerFactory->create()->load($userId, 'user_id');
            $dealer->setUserId($userId);
        }
        return $dealer;
    }
}