<?php namespace BoostMyShop\AdvancedStock\Block\LowStock\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_storeManager;
    protected $_imageHelper;
    protected $_configurableHelper;
    protected $_productHelper;
    protected $_productFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $configurableHelper
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param StoreManagerInterface $storemanager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $configurableHelper,
        \Magento\Catalog\Helper\Product $productHelper,
        StoreManagerInterface $storemanager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storemanager;
        $this->_authorization = $context->getAuthorization();
        $this->_productFactory = $productFactory;
        $this->_productHelper = $productHelper;
        $this->_configurableHelper = $configurableHelper;
        $this->_imageHelper = $imageHelper;
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $imageUrl = '';
        $product = $this->_productFactory->create()->load($row->getData('entity_id'));
        if ($product->getImage() != "no_selection") {
            $imageHelper = $this->_imageHelper->init($row, 'product_listing_thumbnail');
            $imageUrl = $imageHelper->getUrl();
        }

        //check with parent if no url
        if (!$imageUrl) {
            $parentIds = $this->_configurableHelper->create()->getParentIdsByChild($row->getData('entity_id'));
            if (isset($parentIds[0])) {
                $parentProduct = $this->_productFactory->create()->load($parentIds[0]);
                $imageHelper = $this->_imageHelper->init($parentProduct, 'product_listing_thumbnail');
                $imageUrl = $imageHelper->getUrl();
            }
        }

        return '<img src="'.$imageUrl.'" />';
    }
}
