<?php namespace BoostMyShop\AdvancedStock\Block\Transfer;

use Magento\Backend\Block\Widget\Grid\Column;

/**
 * Class Grid
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\CollectionFactory
     */
    protected $_transferCollectionFactory;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory
     */
    protected $_warehouseCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\TransferFactory
     */
    protected $_transferFactory;

    protected $_websiteCollectionFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\CollectionFactory $transferCollectionFactory
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory
     * @param \BoostMyShop\AdvancedStock\Model\TransferFactory $transferFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\CollectionFactory $transferCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\TransferFactory $transferFactory,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_transferCollectionFactory = $transferCollectionFactory;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_transferFactory = $transferFactory;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('transferGrid');
        $this->setDefaultSort('st_id');
        $this->setDefaultDir('DESC');
        $this->setTitle(__('Stock Transfers'));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_transferCollectionFactory->create();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn('st_created_at', ['header' => __('Date'), 'index' => 'st_created_at', 'type' => 'datetime']);
        $this->addColumn('st_reference', ['header' => __('Reference'), 'index' => 'st_reference']);

        $this->addColumn(
            'st_from',
            [
                'header' => __('From'),
                'align' => 'center',
                'index' => 'st_from',
                'type' => 'options',
                'options' => $this->getWarehouseOptions()
            ]
        );

        $this->addColumn(
            'st_to',
            [
                'header' => __('To'),
                'align' => 'center',
                'index' => 'st_to',
                'type' => 'options',
                'options' => $this->getWarehouseOptions()
            ]
        );

        $this->addColumn(
            'st_status',
            [
                'header' => __('Status'),
                'align' => 'center',
                'index' => 'st_status',
                'type' => 'options',
                'options' => $this->_transferFactory->create()->getStatuses()
            ]
        );

        $this->addColumn('st_website_id',
            [
                'header' => __('Website'),
                'index' => 'st_website_id',
                'type' => 'options',
                'options' => $this->getWebsiteAsOptions()
            ]
        );

        $this->_eventManager->dispatch('bms_advancedstock_transfer_grid', ['grid' => $this]);

        return parent::_prepareColumns();
    }


    protected function getWebsiteAsOptions()
    {
        $options = [];
        foreach($this->_websiteCollectionFactory->create() as $item)
        {
            $options[$item->getId()] = $item->getName();
        }
        return $options;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }

    /**
     * @return array $options
     */
    public function getWarehouseOptions()
    {
        $options = [];
        foreach($this->_warehouseCollectionFactory->create() as $item)
        {
            $options[$item->getId()] = $item->getw_name();
        }
        return $options;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $item
     * @return string
     */
    public function getRowUrl($item){

        return $this->getUrl('advancedstock/transfer/edit', ['id' => $item->getst_id()]);

    }

}
