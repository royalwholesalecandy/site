<?php namespace BoostMyShop\AdvancedStock\Block\StockTake;

/**
 * Class Index
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends \Magento\Backend\Block\Widget\Container {

    /**
     * @var string
     */
    protected $_template = 'AdvancedStock/StockTake/Index.phtml';

    /**
     * @return \Magento\Backend\Block\Widget\Container
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'new_stock_take',
            'label' => __('New Stock Take'),
            'class' => 'primary',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button',
            'onclick' => 'setLocation("'.$this->getUrl('advancedstock/stocktake/edit').'")',
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

}