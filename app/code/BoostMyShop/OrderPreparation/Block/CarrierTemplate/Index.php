<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate;

use Magento\Backend\Block\Widget\Grid\Column;

class Index extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
    protected $_config = null;
    protected $_collectionFactory = null;

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
        \BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;

        parent::__construct($context, $backendHelper, $data);

        $this->setPagerVisibility(false);
        $this->setMessageBlockVisibility(false);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setDefaultSort('ct_id');
        $this->setDefaultDir('desc');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {

        $collection = $this->_collectionFactory->create();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('ct_id', ['header' => __('#'), 'index' => 'ct_id']);
        $this->addColumn('ct_created_at', ['header' => __('Date'), 'index' => 'ct_created_at']);
        $this->addColumn('ct_name', ['header' => __('Name'), 'index' => 'ct_name']);
        $this->addColumn('ct_shipping_methods', ['header' => __('Associated shipping methods'), 'index' => 'ct_shipping_methods']);

        return parent::_prepareColumns();
    }

    public function getMainButtonsHtml()
    {
        //nothing, hide buttons
    }

}
