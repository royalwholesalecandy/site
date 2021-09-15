<?php

namespace BoostMyShop\AdvancedStock\Block\Warehouse;


class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'w_id';
        $this->_controller = 'Warehouse';
        $this->_blockGroup = 'BoostMyShop_AdvancedStock';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Warehouse'));
        $this->buttonList->update('delete', 'label', __('Delete Warehouse'));

    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('current_warehouse')->getId()) {
            $supplierName = $this->escapeHtml($this->_coreRegistry->registry('current_warehouse')->getStockName());
            return __("Edit Warehouse '%1'", $supplierName);
        } else {
            return __('New Warehouse');
        }
    }

    /**
     * Return validation url for edit form
     *
     * @return string
     */
    public function getValidationUrl()
    {
        //return $this->getUrl('adminhtml/*/validate', ['_current' => true]);
    }

}
