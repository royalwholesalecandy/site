<?php

namespace BoostMyShop\OrderPreparation\Block\Packing\EditItem;

use Magento\Backend\Block\Widget\Grid\Column;

class Substitution extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_productFactory = null;

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
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_productFactory = $productFactory;
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
        $this->setId('productsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setTitle(__('Add products to purchase order'));
        $this->setUseAjax(true);

    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('image');
        $collection->addAttributeToSelect('thumbnail');

        $collection->addFieldToFilter('type_id', array('in' => array('simple')));


        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn('image', ['header' => __('Image'),'filter' => false, 'sortable' => false, 'type' => 'renderer', 'renderer' => '\BoostMyShop\OrderPreparation\Block\Packing\EditItem\Renderer\Image']);
        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'type' => 'text', 'renderer' => '\BoostMyShop\OrderPreparation\Block\Packing\EditItem\Renderer\Sku']);
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name', 'type' => 'text']);
        //$this->addColumn('qty', ['header' => __('Stock'), 'index' => 'qty', 'type' => 'text']);
        $this->addColumn('select', ['header' => __('Select'), 'index' => 'name', 'type' => 'text', 'align' => 'center', 'filter' => false, 'sortable' => false, 'renderer' => '\BoostMyShop\OrderPreparation\Block\Packing\EditItem\Renderer\Select']);

        return parent::_prepareColumns();
    }


    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/substitutionGrid');
    }

}
