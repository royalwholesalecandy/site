<?php
namespace BoostMyShop\Organizer\Block\Organizer;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    protected $_organizerCollectionFactory;
    protected $_organizer;
    private $_objType = null;
    private $_objId = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \BoostMyShop\Organizer\Model\organizerFactory $organizerFactory
     * @param \BoostMyShop\Organizer\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\Organizer\Model\ResourceModel\Organizer\CollectionFactory $organizerCollectionFactory,
        \BoostMyShop\Organizer\Model\Organizer $organizer,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_organizerCollectionFactory = $organizerCollectionFactory;
        $this->_organizer = $organizer;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('OrganizerGrid');
        $this->setDefaultSort('o_created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
        $this->_parentTemplate = $this->getTemplate();
        $this->setTemplate('Organizer/Grid.phtml');
    }
    

    public function setOrganizerContext($ObjType, $ObjId)
    {
        $this->_objType = $ObjType;
        $this->_objId = $ObjId;
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_organizerCollectionFactory->create();
        if($this->isObjectExist($this->_objType, $this->_objId))
            $collection->addObjectFilter($this->_objType, $this->_objId);
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'o_id',
            [
                'header' => __('Id'),
                'index' => 'o_id',
                'type'  => 'number',
                'width' => '20px'
            ]
        );

        $this->addColumn(
            'o_created_at',
            [
                'header' => __('Created at'),
                'index' => 'o_created_at',
                'type' => 'date'
            ]
        );

        $this->addColumn(
            'o_author_user_id',
            [
                'header' => __('Author'),
                'index' => 'o_author_user_id',
                'type' => 'options', 
                'options' => $this->_organizer->getUsers(),
            ]
        );

        $this->addColumn(
            'o_assign_to_user_id',
            [
                'header' => __('Assigned to'),
                'index' => 'o_assign_to_user_id',
                'type' => 'options', 
                'options' => $this->_organizer->getUsers()
            ]
        );
        if(!$this->isObjectExist($this->_objType, $this->_objId)){

            $this->addColumn(
                'o_object_type',
                [
                    'header' => __('Object type'),
                    'index' => 'o_object_type',
                    'type' => 'options', 
                    'options' => $this->_organizer->getObjectTypes()
                ]
            );

            $this->addColumn(
                'o_object_description',
                [
                    'header' => __('Object description'),
                    'index' => 'o_object_description',
                    'renderer' => '\BoostMyShop\Organizer\Block\Widget\Grid\Column\Renderer\Description'
                ]
            );
        }

        $this->addColumn(
            'o_category',
            [
                'header' => __('Category'),
                'index' => 'o_category',
                'type' => 'options', 
                'options' => $this->_organizer->getCategories()
            ]
        );
        
        $this->addColumn(
            'o_title',
            [
                'header' => __('Label'),
                'index' => 'o_title',
            ]
        );

        $this->addColumn(
            'o_priority',
            [
                'header' => __('Priority'),
                'index' => 'o_priority',
                'type' => 'options', 
                'options' => $this->_organizer->getPriorities()
            ]
        );

        $this->addColumn(
            'o_due_date',
            [
                'header' => __('Due date'),
                'index' => 'o_due_date',
                'type' => 'date'
            ]
        );

        $this->addColumn(
            'o_notified_at',
            [
                'header' => __('Last notified at'),
                'index' => 'o_notified_at',
                'type' => 'date'
            ]
        );

        $this->addColumn(
            'o_status',
            [
                'header' => __('Status'),
                'index' => 'o_status',
                'type' => 'options', 
                'options' => $this->_organizer->getStatuses()
            ]
        );
        
        $this->addColumn(
            'action', 
            [
                'header' => __('Action'),
                'sortable'=> false, 
                'filter'=>false, 
                'align' => 'center',
                'type' => 'action',
                'renderer' => '\BoostMyShop\Organizer\Block\Widget\Grid\Column\Renderer\Popup'
            ]
        );
        
        
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }
        

    public function getGridParentHtml()
    {
        $templateName = $this->resolver->getTemplateFileName($this->_parentTemplate, ['_relative' => true]);
        return $this->fetchView($templateName);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('organizer/organizer/refreshList', ['obj_type'=>$this->_objType, 
                                'obj_id' => $this->_objId,]);
    }

    /**
     * @param \BoostMyShop\Organizer\Model\organizer|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        //
    }

    public function isObjectExist($objType, $objId)
    {
        if($objType != '' && $objId != ''){
            return true;
        }
        return false;
    }

}