<?php

namespace BoostMyShop\Supplier\Block\Supplier;


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
        $this->_objectId = 'sup_id';
        $this->_controller = 'Supplier';
        $this->_blockGroup = 'BoostMyShop_Supplier';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Supplier'));
        $this->buttonList->update('save', 'onclick', "jQuery('productsGrid_massaction-select').removeClass('required-entry');");    //prevent a "required entry" message when saving the form (due to mass action in products grid)
        $this->buttonList->update('delete', 'label', __('Delete Supplier'));

    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('current_supplier')->getId()) {
            $supplierName = $this->escapeHtml($this->_coreRegistry->registry('current_supplier')->getSupName());
            return __("Edit Supplier '%1'", $supplierName);
        } else {
            return __('New Supplier');
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
