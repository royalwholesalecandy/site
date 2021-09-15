<?php

namespace BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Children extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_coreRegistry;
    protected $_childrenCollectionFactory;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Product\Children\CollectionFactory $childrenCollectionFactory,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_coreRegistry = $coreRegistry;
        $this->_childrenCollectionFactory = $childrenCollectionFactory;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('children');
        $this->setDefaultSort('sku');
        $this->setDefaultDir('ASC');
        $this->setTitle(__('Children products'));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_childrenCollectionFactory->create()->addParentFilter($this->getProduct());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'renderer' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Renderer\Sku']);
        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);
        $this->addColumn('stock_details', ['header' => __('Stock details'), 'filter_index' => 'entity_id', 'sortable' => false, 'index' => 'entity_id', 'align' => 'left', 'renderer' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Renderer\StockDetails', 'filter' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Filter\StockDetails']);
        $this->addColumn('website_status', ['header' => __('Website status'), 'filter_index' => 'entity_id', 'sortable' => false, 'index' => 'entity_id', 'align' => 'left', 'renderer' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Renderer\WebsiteStatus', 'filter' => false]);

        return parent::_prepareColumns();
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('advancedstock/erpproduct_children/grid', ['product_id' => $this->getProduct()->getId()]);
    }

    public function getRowUrl($item){
        //empty to not get link to #
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }



    public function getTabLabel()
    {
        return __('Children products');
    }

    public function getTabTitle()
    {
        return __('Children products');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        $excludedProductTypes = ['configurable', 'bundle','grouped'];

        if (in_array($this->getProduct()->getTypeId(), $excludedProductTypes))
            return false;
        else
            return true;
    }

}
