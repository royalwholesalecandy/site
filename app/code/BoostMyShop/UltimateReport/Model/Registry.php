<?php

namespace BoostMyShop\UltimateReport\Model;

class Registry
{
    protected $_adminSession;
    protected $_storeManager;
    protected $_dateTime;
    protected $_userFactory;
    protected $_storeFactory;

    public function __construct(
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_adminSession = $adminSession;
        $this->_storeManager = $storeManager;
        $this->_dateTime = $dateTime;
        $this->_userFactory = $userFactory;
        $this->_storeFactory = $storeFactory;
    }

    public function getFilters()
    {
        $filters = $this->getRegistry('filters');
        if (!$filters)
            $filters = [];
        return $filters;
    }

    public function getRegistry($key)
    {
        $extra = $this->getUserExtra();
        $value = '';
        if (isset($extra['ultimatereport'][$key]))
            $value = $extra['ultimatereport'][$key];
        else
        {
            //check for default value
            switch($key)
            {
                case 'interval':
                    $value = 'current_month';
                    break;
                case 'store':
                    $value = '';
                    break;
            }
        }

        return $value;
    }

    public function setRegistry($key, $value)
    {
        $extra = $this->getUserExtra();
        $extra['ultimatereport'][$key] = $value;
        $this->_adminSession->getUser()->setExtra(serialize($extra));
        $this->_adminSession->getUser()->saveExtra($extra);

        return $this;
    }

    protected function getUserExtra()
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
            if (!isset($extra['ultimatereport']))
                $extra['ultimatereport'] = [];
        }
        catch(\Exception $ex)
        {
            return [];
        }

        return $extra;
    }

}
