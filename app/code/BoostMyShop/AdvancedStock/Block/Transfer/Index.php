<?php namespace BoostMyShop\AdvancedStock\Block\Transfer;

/**
 * Class Index
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends \Magento\Backend\Block\Widget\Container {

    /**
     * @var string
     */
    protected $_template = 'AdvancedStock/Transfer/Index.phtml';

    /**
     * @return \Magento\Backend\Block\Widget\Container
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'new_transfer',
            'label' => __('New'),
            'class' => 'primary',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button',
            'onclick' => 'setLocation("'.$this->getUrl('advancedstock/transfer/edit').'")',
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

}