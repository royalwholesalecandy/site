<?php namespace BoostMyShop\AdvancedStock\Block\StockTake\Widget\Grid\Column\Renderer\Edit\Tabs\Products;

/**
 * Class Sku
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake\Widget\Grid\Column\Renderer\Edit\Tabs\Products
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Sku extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row) {

        $url = $this->getUrl('catalog/product/edit', ['id' => $row->getRelatedProduct()->getId(), 'active_tab' => '']);
        return '<a href="'.$url.'">'.$row->getstai_sku().'</a>';

    }

}