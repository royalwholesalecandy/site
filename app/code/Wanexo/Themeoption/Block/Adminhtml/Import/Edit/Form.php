<?php
namespace Wanexo\Themeoption\Block\Adminhtml\Import\Edit;
use Magento\Config\Model\Config\Source\Yesno;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{ 
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_yesno;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Wanexo\Themeoption\Model\System\Config\Source\Export\ExportFolders
     */
    protected $_exportFolders;

    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @var \Wanexo\Themeoption\Model\System\Config\Source\Import
     */
    protected $_importFiles;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Wanexo\Themeoption\Model\System\Config\Source\Export\ExportFolders $exportFolders,
        \Magento\Store\Model\System\Store $systemStore,
        \Wanexo\Themeoption\Model\System\Config\Source\Import\Files $importFiles,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = []
    ) {
         
        parent::__construct($context, $registry, $formFactory, $data);
        
        $this->_yesno = $yesno;
        $this->_exportFolders = $exportFolders;
        $this->_systemStore = $systemStore;
        $this->_importFiles = $importFiles;
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    { //die('now here!1');
        /**
         * Checking if user have permission to save information
         */
        if($this->_isAllowedAction('Wanexo_Themeoption::import')){
            $isElementDisabled = false;
        }else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
                [
                    'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                    ]
                ]
            );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Wanexo Themeoption Import')]);

        $folders = $this->_exportFolders->toArray();
        $fds = [];
        foreach ($folders as $k => $v) {
            $k = strtolower(str_replace("/", "-", $k));
            $fds[$k] = $v;
        }

        $fieldset->addField(
            'folder',
            'select',
                [
                    'label' => __('Theme Folder'),
                    'title' => __('Folder'),
                    'name' => 'folder',
                    'options' => $fds,
                    'disabled' => $isElementDisabled,
                    'class' => 'wanexo-import'
                ]
        );
        foreach ($this->_exportFolders->toArray() as $key => $val) {
            $orginKey = $key;
            $key = strtolower(str_replace("/", "-", $key));
            $fieldPreset = $fieldset->addField($key,
                'select', [
                    'name'      => $key,
                    'label'     => __('Select File to Import'),
                    'title'     => __('Select File to Import'),
                    'values'    => $this->_importFiles->toOptionArray($orginKey),
                    'note' => '
                    Folder: <strong>app/design/frontend/'.$orginKey.'/backup</strong>
                    <script type="text/javascript">
                    require(["jquery"], function(){
                        jQuery("#'.$key.'").parents(".admin__field").hide();
                        jQuery(".wanexo-import").change(function(){
                            var folder_name = jQuery(this).val();
                            if(folder_name!="'.$key.'"){
                                jQuery("#'.$key.'").parents(".admin__field").hide();    
                            }else{
                                jQuery("#'.$key.'").parents(".admin__field").show();    
                            }
                        }).change();
                        jQuery("#'.$key.'").change(function(){
                            var data_import_file = jQuery(this).val();
                            if(data_import_file == "data_import_file"){
                                jQuery("#data_import_file").parents(".admin__field").show();
                            }else{
                                jQuery("#data_import_file").parents(".admin__field").hide();
                            }
                        }).change();
                    });</script>'
                ]);
        }

        $fieldset->addField(
            'data_import_file',
            'file',
            [
                'name' => 'data_import_file',
                'label' => __('Upload Custom File'),
                'title' => __('Upload Custom File'),
                'after_element_html' => '
                <script type="text/javascript">
                    require(["jquery"], function(){
                        jQuery("#data_import_file").parents(".admin__field").hide();
                    });
                </script>
                '
            ]
        );

        $fieldset->addField(
            'overwrite_blocks',
            'select',
            [
                'name' => 'overwrite_blocks',
                'label' => __('Overwrite Existing Blocks'),
                'title' => __('Overwrite Existing Blocks'),
                'values' => $this->_yesno->toArray(),
                'note' => __('If set to <b>Yes</b>, the import data will override exist data.<br><br>If set to <b>No</b>, the function import will empty data of all table of <b>CMS Page</b> and <b>Static Block</b>, then insert import data.')
            ]
        );

        $field = $fieldset->addField(
                'store_id',
                'select',
                [
                    'name' => 'store_id',
                    'label' => __('Configuration Scope'),
                    'title' => __('Configuration Scope'),
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true),
                    'disabled' => $isElementDisabled
                ]
            );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}