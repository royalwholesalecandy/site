<?php namespace BoostMyShop\AdvancedStock\Block\StockTake\Widget\Grid\Column\Renderer\Apply;

/**
 * Class StockMovementMessage
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake\Widget\Grid\Column\Renderer\Apply
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StockMovementMessage extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * StockMovementMessage constructor.
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row) {

        $row->setData('sta_warehouse_id', $this->_coreRegistry->registry('current_stocktake')->getsta_warehouse_id());
        $qty = $row->getstai_expected_qty() - $row->getstai_scanned_qty();

        return $row->getStockMovementMessage($qty);

    }

}