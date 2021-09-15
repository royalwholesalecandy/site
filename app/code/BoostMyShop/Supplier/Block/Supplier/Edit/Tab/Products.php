<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;

    protected $_productsFactory = null;

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
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Product\AllFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);

        $this->_productsFactory = $productFactory;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('sp_id');
        $this->setDefaultDir('desc');
        $this->setTitle(__('Products'));
        $this->setUseAjax(true);
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
        $collection = $this->_productsFactory->create();
        $collection->addSupplierFilter($this->getSupplier()->getId());
        $collection->addAssociatedFilter();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {


        $this->addColumn('associated', ['header' => __('Associated'), 'index' => 'associated', 'sortable' => false, 'type' => 'options', 'align' => 'center', 'options' => [0 => __('No'), 1 => __('Yes')]]);

        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'renderer' => '\BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Products\Renderer\Sku']);
        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);

        $this->addColumn('sp_sku', ['header' => __('Supplier sku'), 'index' => 'sp_sku', 'align' => 'center']);
        $this->addColumn('sp_price', ['header' => __('Buying price'), 'index' => 'sp_price', 'filter' => false, 'align' => 'center']);
        $this->addColumn('sp_primary', ['header' => __('Primary'), 'index' => 'sp_primary', 'filter' => false, 'align' => 'center']);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsGrid', ['sup_id' => $this->getSupplier()->getId()]);
    }


}
