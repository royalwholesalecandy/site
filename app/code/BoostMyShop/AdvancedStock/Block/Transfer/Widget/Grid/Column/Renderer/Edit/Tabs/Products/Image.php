<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products;

/**
 * Class Images
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Image extends \BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Image {

    /**
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct(\Magento\Framework\DataObject $row)
    {
        return $this->_productModel->create()->load($row->getst_product_id());
    }

}