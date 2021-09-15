<?php
namespace Wanexo\Mlayer\Block\Adminhtml\Banner\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Wanexo\Mlayer\Model\Banner\Source\Type;
use Magento\Config\Model\Config\Source\Yesno as BooleanOptions;

class Banner extends GenericForm implements TabInterface
{
    /**
     * @var WysiwygConfig
     */
    protected $wysiwygConfig;
	
	protected $systemStore;

   
    /**
     * @var Type
     */
    protected $typeOptions;

    /**
     * @var BooleanOptions
     */
    protected $booleanOptions;

    /**
     * @param WysiwygConfig $wysiwygConfig
     * @param Type $typeOptions
     * @param BooleanOptions $booleanOptions
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
		Store $systemStore,
        WysiwygConfig $wysiwygConfig,
        Type $typeOptions,
        BooleanOptions $booleanOptions,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    )
    {
        $this->wysiwygConfig    = $wysiwygConfig;
        $this->typeOptions      = $typeOptions;
        $this->booleanOptions   = $booleanOptions;
		$this->systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Wanexo\Mlayer\Model\Banner $banner */
        $banner = $this->_coreRegistry->registry('wanexo_mlayer_banner');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('banner_');
        $form->setFieldNameSuffix('banner');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Banner Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        $fieldset->addType('image', 'Wanexo\Mlayer\Block\Adminhtml\Banner\Helper\Image');
        $fieldset->addType('file', 'Wanexo\Mlayer\Block\Adminhtml\Banner\Helper\File');

        if ($banner->getId()) {
            $fieldset->addField(
                'banner_id',
                'hidden',
                ['name' => 'banner_id']
            );
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name'      => 'title',
                'label'     => __('Title'),
                'title'     => __('Title'),
                'required'  => true,
            ]
        );
        /*$fieldset->addField(
            'url_key',
            'text',
            [
                'name'      => 'url_key',
                'label'     => __('Web URL key'),
                'title'     => __('Web URL'),
            ]
        );*/
		 $fieldset->addField(
            'web_url',
            'text',
            [
                'name'      => 'web_url',
                'label'     => __('Web URL'),
                'title'     => __('Web URL'),
            ]
        );
        if ($this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'name'      => 'stores[]',
                    'value'     => $this->_storeManager->getStore(true)->getId()
                ]
            );
            $banner->setStoreId($this->_storeManager->getStore(true)->getId());
        }
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
            'is_active',
            'select',
            [
                'label'     => __('Is Active'),
                'title'     => __('Is Active'),
                'name'      => 'is_active',
                'required'  => true,
                'options'   => $banner->getAvailableStatuses(),
            ]
        );
		$fieldset->addField(
            'banner_image',
            'image',
            [
                'name'        => 'banner_image',
                'label'       => __('Banner Image'),
                'title'       => __('Banner Image'),
            ]
        );
        $fieldset->addField(
            'banner_content',
            'editor',
            [
                'name'      => 'banner_content',
                'label'     => __('Content'),
                'title'     => __('Content'),
                'style'     => 'height:36em',
                'required'  => false,
                'config'    => $this->wysiwygConfig->getConfig()
            ]
        );
		
		 $fieldset->addField(
            'btntext',
            'text',
            [
                'name'      => 'btntext',
                'label'     => __('Button Text'),
                'title'     => __('Button Text'),
                'required'  => false,
            ]
        );
		
        $fieldset->addField(
            'content_position',
            'select',
            [
                'label'     => __('Content Position'),
                'title'     => __('Content Position'),
                'name'      => 'content_position',
                'required'  => false,
                'options'   => $this->typeOptions->getOptions()
            ]
        );
        
		
        $bannerData = $this->_session->getData('wanexo_mlayer_banner_data', true);
        if ($bannerData) {
            $banner->addData($bannerData);
        } else {
            if (!$banner->getId()) {
                $banner->addData($banner->getDefaultValues());
            }
        }
        $form->addValues($banner->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Banner');
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
