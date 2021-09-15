<?php
namespace Wanexo\Brand\Block\Adminhtml\Brand\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\System\Store;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Config\Model\Config\Source\Yesno as BooleanOptions;

class Brand extends GenericForm implements TabInterface
{
    /**
     * @var WysiwygConfig
     */
    protected $wysiwygConfig;

    
    protected $booleanOptions;

    public function __construct(
        Store $systemStore,
        WysiwygConfig $wysiwygConfig,
        BooleanOptions $booleanOptions,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    )
    {
        $this->wysiwygConfig    = $wysiwygConfig;
        $this->booleanOptions   = $booleanOptions;
        $this->systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
  
        $testimonial = $this->_coreRegistry->registry('wanexo_brand');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('brand_');
        $form->setFieldNameSuffix('brand');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Brand Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        $fieldset->addType('image', 'Wanexo\Brand\Block\Adminhtml\Brand\Helper\Image');

        if ($testimonial->getId()) {
            $fieldset->addField(
                'brand_id',
                'hidden',
                ['name' => 'brand_id']
            );
        }
          
     
       $fieldset->addField(
            'brand_title',
            'text',
            [
                'name'      => 'brand_title',
                'label'     => __('Brand Title'),
                'title'     => __('Brand Title'),
                'required'  => true,
            ]
        );
		
		$fieldset->addField(
            'brand_option_name',
            'text',
            [
                'name'      => 'brand_option_name',
                'label'     => __('Brand Attribute'),
                'title'     => __('Brand Attribute'),
                'required'  => true,
				'comment' => __('Please do not add any space or bold or any special character in name.'),
            ]
        );
		
         $fieldset->addField(
            'brand_image',
            'image',
            [
                'name'        => 'brand_image',
                'label'       => __('Brand Image'),
                'title'       => __('Brand Image'),
            ]
        );
         $fieldset->addField(
            'brand_thumbimage',
            'image',
            [
                'name'        => 'brand_thumbimage',
                'label'       => __('Brand ThumbImage'),
                'title'       => __('Brand ThumbImage'),
            ]
        );
         $field = $fieldset->addField(
            'store_id',
            'multiselect',
            [
                'name'      => 'stores[]',
                'label'     => __('Store View'),
                'title'     => __('Store View'),
                'required'  => true,
                'values'    => $this->systemStore->getStoreValuesForForm(false, true),
            ]
        );
        $fieldset->addField(
            'content',
            'textarea',
            [
                'name'      => 'content',
                'label'     => __('Content'),
                'title'     => __('Content'),
                'rows'      => 10
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'label'     => __('Status'),
                'title'     => __('Status'),
                'name'      => 'status',
                'required'  => true,
                'options'   =>  $testimonial->getAvailableStatuses(),
            ]
        );
        $fieldset->addField(
            'position',
            'text',
            [
                'label'     => __('Position'),
                'title'     => __('Position'),
                'name'      => 'position',
                //'required'  => true,
                //'options'   =>  $testimonial->getAvailableStatuses(),
            ]
        );
       
        $testimonialData = $this->_session->getData('wanexo_brand_data', true);
        if ($testimonialData) {
            $testimonial->addData($testimonialData);
        } else {
            if (!$testimonial->getId()) {
                $testimonial->addData($testimonial->getDefaultValues());
            }
        }
        $form->addValues($testimonial->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Brand');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
