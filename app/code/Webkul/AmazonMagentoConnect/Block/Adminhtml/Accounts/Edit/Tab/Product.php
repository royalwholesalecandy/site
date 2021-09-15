<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab;

use Webkul\AmazonMagentoConnect\Api\ProductMapRepositoryInterface;
use Webkul\AmazonMagentoConnect\Model\Config\Source\CategoriesList;

/**
 * Adminhtml product grid block
 */
class Product extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var CategoriesList
     */
    private $categoriesList;

    /**
     * @var ProductMapRepositoryInterface
     */
    private $productMapRepository;

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
        ProductMapRepositoryInterface $productMapRepository,
        CategoriesList $categoriesList,
        array $data = []
    ) {
        $this->productMapRepository = $productMapRepository;
        $this->categoriesList = $categoriesList;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('amazon_product_map_grid');
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
        $collection = $this->productMapRepository
                        ->getCollectionByAccountId($amzAccountId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', ['header' => __('Product Name'), 'index' => 'name']);

        $this->addColumn('magento_pro_id', ['header' => __('Magento Product Id'), 'index' => 'magento_pro_id']);

        $this->addColumn('amazon_pro_id', ['header' => __('Amazon Pro Id'), 'index' => 'amazon_pro_id','renderer'  => 'Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab\Renderer\AmazonProduct']);

        $this->addColumn('product_type', ['header' => __('Product Type'), 'index' => 'product_type']);

        $this->addColumn('mage_cat_id', ['header' => __('Magento Category'), 'index' => 'mage_cat_id', 'renderer'  => 'Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab\Renderer\MageCategoryName','filter' => false,'filter_condition_callback' => [$this, '_getCateFilter']]);

        $this->addColumn('error_status', ['header' => __('Error Msg'), 'index' => 'error_status','renderer'  => 'Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab\Renderer\ErrorButton','filter' => false,]);
        
        $this->addColumn('pro_status_at_amz', ['header' => __('Product Status'), 'index' => 'pro_status_at_amz', 'renderer'  => 'Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab\Renderer\AmzProStatus','filter_condition_callback' => [$this, '_getAmzProStatusFilter']]);

        $this->addColumn('fulfillment_channel', ['header' => __('Fulfillment Channel'), 'index' => 'fulfillment_channel']);

        $this->addColumn('created_at', ['header' => __('Sync Date'), 'index' => 'created_at', 'type' => 'datetime']);

        return parent::_prepareColumns();
    }

    /**
     * set amazon product id filter
     * @return object
     */
    protected function _myAmzProductFilter($collection, $column)
    {
        $value = trim($column->getFilter()->getValue());
        if ($value!== 'N/A') {
            return $this;
        }

        $this->getCollection()->getSelect()->where(
            "amazon_pro_id IS NULL"
        );

        return $this;
    }

    /**
     * set product status filter
     * @return object
     */
    protected function _getAmzProStatusFilter($collection, $column)
    {
        $value = trim($column->getFilter()->getValue());

        $status = null;
        if (strpos('PENDING', strtoupper($value)) !== false) {
            $status = '3';
        } elseif (strpos('INACTIVE', strtoupper($value)) !== false) {
            $status = '2';
        } elseif (strpos('FAILED', strtoupper($value)) !== false) {
            $status = '0';
        } else {
            $status = '1';
        }
        $this->getCollection()->getSelect()->where(
            "pro_status_at_amz = ?",
            "$status"
        );


        return $this;
    }

    /**
     * set category filter
     * @return object
     */
    protected function _getCateFilter($collection, $column)
    {
        $value = trim($column->getFilter()->getValue());

        $this->getCollection()->getSelect()->where(
            "mage_cat_id like ?",
            "%$value%"
        );


        return $this;
    }

    /**
     * get massaction
     * @return object
     */
    protected function _prepareMassaction()
    {
        $asinCatOptions = $this->categoriesList->toOptionArray();
        $accountId = $this->getRequest()->getParam('id');
        $this->setMassactionIdField('entity_id');
        $this->setChild('massaction', $this->getLayout()->createBlock($this->getMassactionBlockName()));
        $this->getMassactionBlock()->setFormFieldName('productEntityIds');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl(
                    '*/product/massdelete',
                    [
                        'account_id'=>$accountId
                    ]
                ),
                'confirm' => __('Are you sure want to delete?')
            ]
        )->addItem(
            'massassigncate',
            [
                'label'=> __('Assign to category'),
                'url'=> $this->getUrl(
                    '*/product/massassigntocategory',
                    [
                        'account_id'=>$accountId
                    ]
                ),
                'additional'=> [
                    'visibility'=> [
                    'name'=> 'magecate',
                    'type'=> 'select',
                    'label'=> __('Category'),
                    'values'=> $asinCatOptions
                    ]
                ]
            ]
        )->addItem(
            'update_as_FBA_amazon',
            [
                'label' => __('Update as FBA To Amazon'),
                'url' => $this->getUrl(
                    '*/producttoamazon/synctoamazon',
                    [
                        'account_id'=>$accountId, 'is_fba' => 1, 'is_update' => 1
                    ]
                ),
                'confirm' => __('Are you sure want to export magento product to Amazon as fulfillment by Amazon(FBA) ?')
            ]
        )->addItem(
            'update_as_FBM_amazon',
            [
                'label' => __('Update as FBM To Amazon'),
                'url' => $this->getUrl(
                    '*/producttoamazon/synctoamazon',
                    [
                        'account_id'=>$accountId,'is_fba' => 0, 'is_update' => 1
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
        return $this->getUrl('amazonmagentoconnect/product/gridreset', ['_current' => true]);
    }
}
