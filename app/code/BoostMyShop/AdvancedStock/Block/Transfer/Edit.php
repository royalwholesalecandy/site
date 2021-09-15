<?php namespace BoostMyShop\AdvancedStock\Block\Transfer;

/**
 * Class Edit
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Edit constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'Transfer';
        $this->_blockGroup = 'BoostMyShop_AdvancedStock';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->add('save', [
            'id' => 'save',
            'label' => __('Save'),
            'class' => 'primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
            ]
        ]);

        if ($this->getTransfer()->getId()) {
            $this->buttonList->add('print', [
                'id' => 'print',
                'label' => __('Print'),
                'class' => 'print',
                'onclick' => 'setLocation("' . $this->getUrl('advancedstock/transfer/printpdf', ['_current' => true, 'id' => $this->_coreRegistry->registry('current_transfer')->getId()]) . '")'
            ]);

            if ($this->getTransfer()->isOpened()) {
                $this->buttonList->add('add_products_scan', [
                    'id' => 'add_products_scan',
                    'label' => __('Add products with scanner'),
                    'class' => '',
                    'onclick' => 'setLocation("' . $this->getUrl('advancedstock/transfer/addproductsscan', ['_current' => true, 'id' => $this->_coreRegistry->registry('current_transfer')->getId()]) . '")'
                ]);
            }

            if (count($this->getTransfer()->getItems()) > 0) {
                $this->buttonList->add('apply', [
                    'id' => 'apply',
                    'label' => __('Apply'),
                    'class' => '',
                    'onclick' => 'setLocation("' . $this->getUrl('advancedstock/transfer/apply', ['_current' => true, 'id' => $this->_coreRegistry->registry('current_transfer')->getId()]) . '")'
                ]);
            }
        }
    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\Transfer
     */
    public function getTransfer()
    {
        return $this->_coreRegistry->registry('current_transfer');
    }
}
