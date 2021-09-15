<?php
namespace Wanexo\Brand\Model;

use Magento\Framework\Model\AbstractModel;
use Wanexo\Brand\Model\Brand\Source\IsActive;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Data\Collection\AbstractDb;

class Brand extends AbstractModel
{
     const STATUS_ENABLED = 1;
     
     const STATUS_DISABLED = 0;
     
     protected $statusList;
     
     public function __construct(
       
        IsActive $statusList,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->statusList = $statusList;
        parent::__construct($context,$registry, $resource, $resourceCollection, $data);
    }
    
    protected function _construct()
    {
        $this->_init('Wanexo\Brand\Model\ResourceModel\Brand');
    }
    
    public function getDefaultValues()
    {
        return ['status' => self::STATUS_ENABLED];
    }
    
     public function getAvailableStatuses()
    {
        return $this->statusList->getOptions();
    }
    
    public function isActive()
    {
        return (bool)$this->getIsActive();
    }
 }
 
 ?>