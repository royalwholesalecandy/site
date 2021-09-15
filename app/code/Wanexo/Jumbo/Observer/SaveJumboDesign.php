<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wanexo\Jumbo\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveJumboDesign implements ObserverInterface
{
    protected $_messageManager;
    protected $_cssGenerator;
    
    /**
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Wanexo\Jumbo\Model\Stylesetting\Generator $cssenerator,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_cssGenerator = $cssenerator;
        $this->_messageManager = $messageManager;
    }

    /**
     * Log out user and redirect to new admin custom url
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $message = 'Saved Jumbo Design...';
        $this->_cssGenerator->generateCss('theme_style', $observer->getData("website"), $observer->getData("store"));
    }
}
