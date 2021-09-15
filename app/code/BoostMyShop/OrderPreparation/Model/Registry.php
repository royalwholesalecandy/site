<?php

namespace BoostMyShop\OrderPreparation\Model;

class Registry
{
    protected $_adminSession;
    protected $_logger;

    public function __construct(
        \Magento\Backend\Model\Auth\Session $adminSession,
        \BoostMyShop\OrderPreparation\Helper\Logger $logger
    ) {
        $this->_adminSession = $adminSession;
        $this->_logger = $logger;
    }

    public function getCurrentOperatorId()
    {
        return $this->getRegistry('current_operator_id');
    }

    public function getCurrentWarehouseId()
    {
        return $this->getRegistry('current_warehouse_id');
    }

    public function changeCurrentWarehouseId($warehouseId)
    {
        $this->setRegistry('current_warehouse_id', $warehouseId);
    }

    public function changeCurrentOperatorId($operatorId)
    {
        $this->setRegistry('current_operator_id', $operatorId);
    }

    public function getRegistry($key)
    {
        $extra = $this->getUserExtra();
        $value = '';
        if (isset($extra['orderpreparation'][$key]))
            $value = $extra['orderpreparation'][$key];
        else
        {
            switch($key)
            {
                case 'current_operator_id':
                    $value = $this->_adminSession->getUser() ? $this->_adminSession->getUser()->getId() : -1;
                    break;
                case 'current_warehouse_id':
                    $value = 1;
                    break;
            }
        }

        $this->_logger->log('Get '.$key.' : '.$value, 'registry');

        return $value;
    }

    public function setRegistry($key, $value)
    {
        $extra = $this->getUserExtra();
        $extra['orderpreparation'][$key] = $value;
        $this->_adminSession->getUser()->setExtra(serialize($extra));
        $this->_adminSession->getUser()->saveExtra($extra);

        $this->_logger->log('Change '.$key.' to '.$value, 'registry');

        return $this;
    }

    public function getUserExtra()
    {
        if (!$this->_adminSession)
            return [];
        if (!$this->_adminSession->getUser())
            return [];
        $extra = $this->_adminSession->getUser()->getExtra();
        try
        {
            if (is_string($extra))
                $extra = unserialize($extra);
            if (!is_array($extra))
                $extra = [];
            if (!isset($extra['orderpreparation']))
                $extra['orderpreparation'] = [];
        }
        catch(\Exception $ex)
        {
            return [];
        }
        
        return $extra;
    }

}
