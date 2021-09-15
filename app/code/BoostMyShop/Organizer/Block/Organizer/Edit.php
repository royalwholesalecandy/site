<?php

namespace BoostMyShop\Organizer\Block\Organizer;

class Edit extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    private $_task = null;
    private $_objType = null;
    private $_objId = null;
    protected $_organizer;
    protected $_objectType;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\Organizer\Model\OrganizerFactory $organizerFactory,
        \BoostMyShop\Organizer\Model\Organizer $organizer,
        \BoostMyShop\Organizer\Model\ObjectType $objectType,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_organizerFactory = $organizerFactory;
        $this->_organizer = $organizer;
        $this->_objectType = $objectType;
        parent::__construct($context, $data);
    }

    public function setoId($oId)
    {
        if ($oId != '')
        {
            $model = $this->_organizerFactory->create();
            $this->_task = $model->load($oId);                 
        }
        return $this;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    
    public function getTask()
    {
        if ($this->_task == null)
        {
            $this->_task = $this->_organizerFactory->create();
            $this->_task->seto_object_type($this->_objType);
            $this->_task->seto_object_id($this->_objId);
            $this->_task->seto_object_description($this->_objectType->getObjectLabel($this->_objType, $this->_objId));
        }
        return $this->_task;
    }
    
    public function getObjectUrl()
    {
        return $this->_objectType->getObjectUrl($this->_objType, $this->_objId);
    }

    public function setOrganizerContext($ObjType, $ObjId)
    {
        $this->_objType = $ObjType;
        $this->_objId = $ObjId;
        return $this;
    }

    public function getUsers()
    {
        return $this->_organizer->getUsers();
    }

    public function getCategories()
    {
        return $this->_organizer->getCategories();
    }

    public function getPriorities()
    {
        return $this->_organizer->getPriorities();
    }

    public function getStatuses()
    {
        return $this->_organizer->getStatuses();
    }

}