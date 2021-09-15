<?php namespace BoostMyShop\AdvancedStock\Block\StockTake\Scan;

/**
 * Class Products
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake\Scan
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Products extends \Magento\Backend\Block\Widget\Container {

    /**
     * @var string
     */
    protected $_template = 'AdvancedStock/StockTake/Scan/Products.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_stockTakeItemCollection;

    /**
     * Products constructor.
     * @param \BoostMyShop\AdvancedStock\Model\Config $config
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_imageHelper = $imageHelper;
        $this->_config = $config;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {

        $this->buttonList->add('back',
            [
                'id' => 'back',
                'label' => __('Back'),
                'class' => 'back',
                'onclick' => 'window.setLocation("'.$this->getBackUrl().'")'
            ]
        );

        $this->buttonList->add('save',
            [
                'id' => 'save',
                'label' => __('Save'),
                'class' => 'primary',
                'onclick' => 'jQuery(\'#edit_form\').submit();'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * @return string $url
     */
    public function getBackUrl(){

        if ($this->getStockTake()->getsta_per_location() == 1) {

            $url = $this->getUrl('*/*/scanPerLocation', ['_current' => true, 'id' => $this->getStockTake()->getId()]);

        } else {

            $url = $this->getUrl('*/*/edit', ['_current' => true, 'id' => $this->getStockTake()->getId()]);

        }

        return $url;

    }

    /**
     * @return string
     */
    public function getLocation(){
        return $this->_coreRegistry->registry('current_stock_take_location');
    }

    /**
     * @return string
     */
    public function getSubmitUrl()
    {
        if ($this->getStockTake()->getsta_per_location() == 1) {

            $url = $this->getUrl('*/stocktake_scan/Save', ['id' => $this->getStockTake()->getId(), 'saveAndScanLocation' => true]);

        } else {

            $url = $this->getUrl('*/stocktake_scan/Save', ['id' => $this->getStockTake()->getId()]);

        }
        return $url;
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\StockTake
     */
    public function getStockTake()
    {
        return $this->_coreRegistry->registry('current_stocktake');
    }

    /**
     * @param \Magento\Catalog\Model\Product $item
     * @return string $imageUrl
     */
    public function getImageUrl($item)
    {
        $imageHelper = $this->_imageHelper->init($item, 'product_listing_thumbnail');
        $imageUrl = $imageHelper->getUrl();
        return $imageUrl;
    }

    /**
     * @param \BoostMyShop\AdvancedStock\Model\StockTake\Item $item
     * @return string
     */
    public function getProductUrl($item)
    {
        return $this->getUrl('catalog/product/edit', ['id' => $item->getId()]);
    }

    /**
     * @return string
     */
    public function getProductIdsJson()
    {
        return json_encode($this->getItems()->getColumnValues('entity_id'));
    }

    /**
     * @return string
     */
    public function getBarcodesJson()
    {
        $barcodes = [];

        foreach($this->getItems() as $item){

            if($item->getData($this->_config->getBarcodeAttribute()))
                $barcodes[$item->getData($this->_config->getBarcodeAttribute())] = $item->getentity_id();

        }

        return json_encode($barcodes);

    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getItems(){

        if(is_null($this->_stockTakeItemCollection)){

            $this->_stockTakeItemCollection = $this->getStockTake()->getItemsForScan();

            if(!is_null($this->getLocation())){

                $this->_stockTakeItemCollection->addFieldToFilter('stai_location', $this->getLocation());

            }

        }

        return $this->_stockTakeItemCollection;

    }

    /**
     * @return int
     */
    public function getPendingQty($item){

        if($item->getstai_expected_qty() == max($item->getstai_expected_qty(), $item->getstai_scanned_qty()))
            return $item->getstai_expected_qty() - $item->getstai_scanned_qty();
        else
            return 0;

    }

}