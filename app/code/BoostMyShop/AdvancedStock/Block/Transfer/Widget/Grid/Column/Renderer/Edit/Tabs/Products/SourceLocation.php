<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products;

/**
 * Class SourceLocation
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SourceLocation extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row){

        $html = '';
        $html .= __('Qty').' : '.$row->getTransferItem()->getSourceWarehouse()->getwi_physical_quantity().'<br/>';
        if ($row->getTransferItem()->getSourceWarehouse()->getwi_shelf_location())
            $html .= __('Location').' : '.$row->getTransferItem()->getSourceWarehouse()->getwi_shelf_location();
        return $html;

    }

}