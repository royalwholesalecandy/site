<?php

namespace Metagento\Core\Observer\Adminhtml;


class ControllerActionPredispatch implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @param Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Metagento\Core\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Metagento\Core\Helper\Data $helper,
        \Metagento\Core\Model\FeedFactory $feedFactory
    ) {
        $this->_backendAuthSession = $backendAuthSession;
        $this->helper              = $helper;
        $this->feedFactory         = $feedFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_backendAuthSession->isLoggedIn()
            && $this->helper->isModuleOutputEnabled('Magento_AdminNotification')
        ) {
            /** @var \Metagento\Core\Model\Feed $feed */
            $feed = $this->feedFactory->create();
            $feed->checkUpdate();
        }
    }
}
