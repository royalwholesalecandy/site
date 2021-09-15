<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\Ticket;

use Magenest\QuickBooksDesktop\Controller\Adminhtml\Ticket as AbstractTicket;

/**
 * Class Index
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\Ticket
 */
class Index extends AbstractTicket
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Magenest_QuickBooksDesktop::ticket');
        $resultPage->addBreadcrumb(__('Manage Ticket'), __('Manage Ticket'));
        $resultPage->addBreadcrumb(__('Manage Ticket'), __('Manage Ticket'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Ticket'));

        return $resultPage;
    }
}
