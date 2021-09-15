<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate;


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
        $this->_objectId = 'ct_id';
        $this->_controller = 'CarrierTemplate';
        $this->_blockGroup = 'BoostMyShop_OrderPreparation';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save template'));
        $this->buttonList->update('delete', 'label', __('Delete template'));

    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('current_carrier_template')->getId()) {
            $ctName = $this->escapeHtml($this->_coreRegistry->registry('current_carrier_template')->getCtName());
            return __("Edit Template '%1'", $ctName);
        } else {
            return __('New Template');
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
