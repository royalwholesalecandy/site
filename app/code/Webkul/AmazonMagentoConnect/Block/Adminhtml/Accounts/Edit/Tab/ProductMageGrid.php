<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Webkul\AmazonMagentoConnect\Model\ProductMapFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductMageGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var ProductMapFactory
     */
    private $productMap;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $productVisibility;

    /**
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Backend\Helper\Data              $backendHelper
     * @param CollectionFactory                         $productCollectionFactory
     * @param ProductmapFactory                         $productMap
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CollectionFactory $productCollectionFactory,
        ProductMapFactory $productMap,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        array $data = []
    ) {
        $this->productMap = $productMap;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mage_map_product');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $mappedProId = [];
        $accountId = $this->getRequest()->getParam('id');
        $collection = $this->productMap->create()->getCollection();
        $collection->addFieldToFilter('mage_amz_account_id', ['eq' => $accountId]);
        foreach ($collection as $product) {
            $mappedProId[] = $product->getMagentoProId();
        }

        $mageProCollection = $this->productCollectionFactory
                            ->create()
                            ->addAttributeToSelect('*')
                            ->addFieldToFilter(
                                'type_id',
                                ['in' => ['simple']]
                            );
        if (!empty($mappedProId)) {
            $mageProCollection->addFieldToFilter(
                'entity_id',
                ['nin'=>$mappedProId]
            );
        }
        $mageProCollection->setVisibility($this->productVisibility->getVisibleInSiteIds());
        $this->setCollection($mageProCollection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Id'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'sortable' => true,
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'identification_value',
            [
                'header' => __('Product Identifier'),
                'sortable' => true,
                'index' => 'identification_value'
            ]
        );
        $this->addColumn(
            'type_id',
            [
                'header' => __('Type'),
                'sortable' => true,
                'index' => 'type_id'
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'sortable' => false,
                'index' => 'sku'
            ]
        );
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
        $this->setChild('massaction', $this->getLayout()->createBlock($this->getMassactionBlockName()));
        $this->getMassactionBlock()->setFormFieldName('mageProEntityIds');
        $this->getMassactionBlock()->addItem(
            'export_as_FBA_amazon',
            [
                'label' => __('Export as FBA To Amazon'),
                'url' => $this->getUrl(
                    '*/producttoamazon/synctoamazon',
                    [
                        'account_id'=>$accountId, 'is_fba' => 1
                    ]
                ),
                'confirm' => __('Are you sure want to export magento product to Amazon as fulfillment by Amazon(FBA) ?')
            ]
        );
        $this->getMassactionBlock()->addItem(
            'export_as_FBM_amazon',
            [
                'label' => __('Export as FBM To Amazon'),
                'url' => $this->getUrl(
                    '*/producttoamazon/synctoamazon',
                    [
                        'account_id'=>$accountId,'is_fba' => 0
                    ]
                ),
                'confirm' => __('Are you sure want to export magento product to Amazon as fulfillment by Merchant(FBM) ?')
            ]
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('amazonmagentoconnect/producttoamazon/gridreset', ['_current' => true]);
    }
}
