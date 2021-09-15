<?php namespace BoostMyShop\AdvancedStock\Block\StockTake;

/**
 * Class Apply
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Apply extends \Magento\Backend\Block\Widget\Container {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var string
     */
    protected $_template = 'AdvancedStock/StockTake/Apply.phtml';

    /**
     * Apply constructor.
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->buttonList->add('apply',
            [
                'id' => 'apply',
                'label' => __('Create stock movements'),
                'class' => 'primary',
                'onclick' => 'window.setLocation("'.$this->getUrl('*/*/CreateStockMovements', ['_current' => true, 'id' => $this->_coreRegistry->registry('current_stocktake')->getId()]).'")'
            ]
        );

        $this->buttonList->add('back',
            [
                'id' => 'back',
                'label' => __('Back'),
                'class' => 'back',
                'onclick' => 'window.setLocation("'.$this->getUrl('*/*/edit', ['_current' => true, 'id' => $this->_coreRegistry->registry('current_stocktake')->getId()]). '")'
            ]
        );

        return parent::_prepareLayout();
    }

}