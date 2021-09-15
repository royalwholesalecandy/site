<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products;

/**
 * Class Delete
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {

        return '<input type="checkbox" name="transfer[delete]['.$row->getsti_id().']" id="delete_'.$row->getsti_id().'" />';

    }

}