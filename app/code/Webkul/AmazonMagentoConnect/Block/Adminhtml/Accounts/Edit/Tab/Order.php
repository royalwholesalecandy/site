<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab;

/**
 * Adminhtml customer orders grid block
 */
class Order extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    private $collectionFactory;

    /**
     * \Webkul\AmazonMagentoConnect\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('amazon_order_map_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $amzAccountId = $this->getRequest()->getParam('id');
        $collection = $this->collectionFactory->getReport('amazonconnect_order_map_list_data_source')
                                ->addFieldToSelect('entity_id')
                                ->addFieldToSelect('created_at')
                                ->addFieldToSelect('mage_order_id')
                                ->addFieldToSelect('amazon_order_id')
                                ->addFieldToSelect('purchase_date')
                                ->addFieldToSelect('status')
                                ->addFieldToSelect('fulfillment_channel')
                                ->addFieldToSelect('error_msg')
                                ->addFieldToFilter('mage_amz_account_id', $amzAccountId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('mage_order_id', ['header' => __('Magento Order Id'), 'width' => '50px', 'index' => 'mage_order_id']);

        $this->addColumn('amazon_order_id', ['header' => __('Amazon Order Id'), 'index' => 'amazon_order_id']);
        
        $this->addColumn('status', ['header' => __('Order Status'),'width' => '50px', 'index' => 'status']);
        
        $this->addColumn('fulfillment_channel', ['header' => __('Fulfillment Channel'),'width' => '50px', 'index' => 'fulfillment_channel']);

        // $this->addColumn('created_at', ['header' => __('Sync Date'), 'index' => 'created_at', 'type' => 'datetime']);

        $this->addColumn('purchase_date', ['header' => __('Purchase Date'), 'index' => 'purchase_date', 'type' => 'datetime']);

        if ($this->helper->getModuleManager()->isOutputEnabled('Webkul_AmazonMCF')) {
            $this->addColumn('error_msg', ['header' => __('Error Msg'),'width' => '50px', 'index' => 'error_msg']);
        }
        return parent::_prepareColumns();
    }

    /**
     * get massaction
     * @return object
     */
    protected function _prepareMassaction()
    {
        $accountId = $this->getRequest()->getParam('id');
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('orderEntityIds');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl(
                    '*/order/massdelete',
                    [
                        'account_id'=>$accountId
                    ]
                ),
                'confirm' => __('Are you sure want to delete?'),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('amazonmagentoconnect/order/resetgrid', ['_current' => true]);
    }
}
