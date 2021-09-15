<?php namespace BoostMyShop\AdvancedStock\Block\StockTake\Scan;

/**
 * Class Location
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake\Scan
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Location extends \Magento\Backend\Block\Widget\Container {

    /**
     * @var string
     */
    protected $_template = 'AdvancedStock/StockTake/Scan/Location.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Scan constructor.
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {

        $this->buttonList->add('back',
            [
                'id' => 'back',
                'label' => __('Back'),
                'class' => 'back',
                'onclick' => 'window.setLocation("'.$this->getUrl('*/*/edit', ['_current' => true, 'id' => $this->getStockTake()->getId()]).'")'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\StockTake
     */
    public function getStockTake(){

        return $this->_coreRegistry->registry('current_stocktake');

    }

    /**
     * @return string
     */
    public function getProductScanUrl(){

        return $this->getUrl('*/*/scan', ['_current' => true, 'id' => $this->getStockTake()->getId()]);

    }

}