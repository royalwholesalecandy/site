<?php namespace BoostMyShop\AdvancedStock\Block\StockTake\Widget\Grid\Column\Renderer\Edit\Tabs\Products;

/**
 * Class ScannedQty
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake\Widget\Grid\Column\Renderer\Edit\Tabs\Products
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScannedQty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string|void
     */
    public function render(\Magento\Framework\DataObject $row) {

        $html = '<input type="text" name="stocktake[scanned_quantities]['.$row->getstai_id().']" id="scanned_quantities_'.$row->getId().'" value="'.$row->getstai_scanned_qty().'"/>';
        return $html;

    }

}