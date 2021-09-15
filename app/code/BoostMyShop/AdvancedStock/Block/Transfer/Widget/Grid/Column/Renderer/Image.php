<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer;

/**
 * Class Image
 *
 * @package   BoostMyShop\AdvancedStock\Block\Widget\Grid\Column\Renderer
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_productModel;

    /**
     * Image constructor.
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ProductFactory $productModel,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_imageHelper = $imageHelper;
        $this->_productModel = $productModel;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row){

        return '<img src="'.$this->_imageHelper->init($this->getProduct($row), 'product_thumbnail_image')->getUrl().'"/>';

    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Catalog\Model\Product
     */
    abstract function getProduct(\Magento\Framework\DataObject $row);

}