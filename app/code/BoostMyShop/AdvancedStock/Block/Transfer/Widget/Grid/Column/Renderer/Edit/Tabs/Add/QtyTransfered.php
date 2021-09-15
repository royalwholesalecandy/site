<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Add;

/**
 * Class QtyTransfered
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\Wiget\Grid\Column\Renderer
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QtyTransfered extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row){

        return '<input type="text" name="transfer[products]['.$row->getId().'][qty_transfered]" id="qty_transfered_'.$row->getId().'" value="'.$row->getst_qty_transfered().'" />';

    }

}