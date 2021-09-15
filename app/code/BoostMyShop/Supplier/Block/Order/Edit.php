<?php

namespace BoostMyShop\Supplier\Block\Order;


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
        $this->_objectId = 'po_id';
        $this->_controller = 'Order';
        $this->_blockGroup = 'BoostMyShop_Supplier';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->update('save', 'onclick', "jQuery('#po_current_tab').val(jQuery('.ui-tabs-active').attr('aria-labelledby'));");

        $this->buttonList->update('delete', 'label', __('Delete'));
        $this->buttonList->remove('reset');

        if ($this->_coreRegistry->registry('current_purchase_order')->getId())
            $this->addButtons();

    }

    public function addButtons()
    {
        $this->buttonList->add('print', [
            'id' => 'print',
            'label' => __('Print'),
            'class' => 'print',
            'onclick' => 'setLocation(\''.$this->getPrintUrl().'\')'
        ]);

        $this->buttonList->add('notify', [
            'id' => 'notify',
            'label' => __('Notify'),
            'class' => '',
            'onclick' => 'setLocation(\''.$this->getNotifyUrl().'\')'
        ]);

        $this->buttonList->add('update_cost', [
            'id' => 'update_cost',
            'label' => __('Update product costs'),
            'class' => '',
            'onclick' => 'setLocation(\''.$this->getUpdateCostUrl().'\')'
        ]);

        $this->buttonList->add('receive', [
            'id' => 'receive',
            'label' => __('Receive'),
            'class' => '',
            'onclick' => 'setLocation(\''.$this->getReceiveUrl().'\')'
        ]);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('current_purchase_order')->getId()) {
            $supplierName = $this->escapeHtml($this->_coreRegistry->registry('current_purchase_order')->getTitle());
            return __("Edit Purchase Order %1", $supplierName);
        } else {
            return __('New Purchase Order');
        }
    }

    /**
     *
     */
    protected function getPrintUrl()
    {
        return $this->getUrl('*/*/print', ['po_id' => $this->_coreRegistry->registry('current_purchase_order')->getId()]);
    }

    /**
     *
     */
    protected function getReceiveUrl()
    {
        return $this->getUrl('*/*/receive', ['po_id' => $this->_coreRegistry->registry('current_purchase_order')->getId()]);
    }

    /**
     *
     */
    protected function getNotifyUrl()
    {
        return $this->getUrl('*/*/notify', ['po_id' => $this->_coreRegistry->registry('current_purchase_order')->getId()]);
    }

    protected function getUpdateCostUrl()
    {
        return $this->getUrl('*/*/updateCost', ['po_id' => $this->_coreRegistry->registry('current_purchase_order')->getId()]);
    }

}
