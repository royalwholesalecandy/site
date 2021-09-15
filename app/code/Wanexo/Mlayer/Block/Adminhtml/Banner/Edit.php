<?php
namespace Wanexo\Mlayer\Block\Adminhtml\Banner;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends FormContainer
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    )
    {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize banner edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'banner_id';
        $this->_blockGroup = 'Wanexo_Mlayer';
        $this->_controller = 'adminhtml_banner';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Banner'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Banner'));
    }

    /**
     * Retrieve text for header element depending on loaded banner
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \Wanexo\Mlayer\Model\Banner $banner */
        $banner = $this->coreRegistry->registry('wanexo_mlayer_banner');
        if ($banner->getId()) {
            return __("Edit Banner '%1'", $this->escapeHtml($banner->getName()));
        }
        return __('New Banner');
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('banner_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'banner_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'banner_content');
                }
            };
        ";
        return parent::_prepareLayout();
    }
}
