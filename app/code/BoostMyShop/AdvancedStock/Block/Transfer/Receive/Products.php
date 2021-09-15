<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Receive;

/**
 * Class Products
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\Receive
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Products extends \Magento\Backend\Block\Template {

    /**
     * @var string
     */
    protected $_template = 'AdvancedStock/Transfer/Receive/Products.phtml';

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

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_imageHelper = $imageHelper;
        $this->_config = $config;
    }

    /**
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/transfer_reception/Save', ['id' => $this->_coreRegistry->registry('current_transfer')->getId()]);
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\Transfer
     */
    public function getTransfer()
    {
        return $this->_coreRegistry->registry('current_transfer');
    }

    /**
     * @param \BoostMyShop\AdvancedStock\Model\Transfer\Item $item
     * @return string $imageUrl
     */
    public function getImageUrl($item)
    {
        $imageHelper = $this->_imageHelper->init($item->getTransferItem()->getRelatedProduct(), 'product_listing_thumbnail');
        $imageUrl = $imageHelper->getUrl();
        return $imageUrl;
    }

    /**
     * @param \BoostMyShop\AdvancedStock\Model\Transfer\Item $item
     * @return string
     */
    public function getProductUrl($item)
    {
        if ($this->_config->isErpIsInstalled())
            $url = $this->getUrl('erp/products/edit', ['id' => $item->getst_product_id()]);
        else
            $url = $this->getUrl('catalog/product/edit', ['id' => $item->getst_product_id()]);

        return $url;
    }

    /**
     * @return string
     */
    public function getProductIdsJson()
    {
        return json_encode($this->getTransfer()->getItems()->getAllIds());
    }

    /**
     * @return string
     */
    public function getBarcodesJson()
    {
        $barcodes = array();
        $barcodeAttribute = $this->_config->getBarcodeAttribute();

        if (!empty($barcodeAttribute))
        {
            foreach($this->getTransfer()->getItems() as $item)
            {
                if ($item->getTransferItem()->getRelatedProduct()->getData($barcodeAttribute))
                {
                    $barcodes[$item->getTransferItem()->getRelatedProduct()->getData($barcodeAttribute)] = $item->getst_product_id();
                }
            }
        }

        return json_encode($barcodes);
    }

    public function getBarcodeAttribute()
    {
        return $this->_config->getBarcodeAttribute();
    }

}