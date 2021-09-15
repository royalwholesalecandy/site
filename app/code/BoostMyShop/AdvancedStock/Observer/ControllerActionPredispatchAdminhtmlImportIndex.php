<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ControllerActionPredispatchAdminhtmlImportIndex implements ObserverInterface
{
    protected $_context;

    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_context = $context;
    }

    public function execute(EventObserver $observer)
    {
        $this->_context->getMessageManager()->addError(__('Warning : if you use this screen to import or update inventory, you must run the ERP stock discrepency tool after to synchronize inventory'));

        return $this;
    }

}
