<?php
namespace Wanexo\Brand\Block\Adminhtml\Brand;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends FormContainer
{
   
    protected $coreRegistry = null;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    )
    {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'brand_id';
        $this->_blockGroup = 'Wanexo_Brand';
        $this->_controller = 'adminhtml_brand';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Brand'));
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
        $this->buttonList->update('delete', 'label', __('Delete Brand'));
    }

    public function getHeaderText()
    {
        
        $author = $this->coreRegistry->registry('wanexo_brand');
        if ($author->getId()) {
            return __("Edit Author '%1'", $this->escapeHtml($author->getAuthor()));
        }
        return __('New Brand');
    }


    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('brand_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'brand_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'brand_content');
                }
            };
        ";
        return parent::_prepareLayout();
    }
}
