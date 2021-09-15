<?php


namespace Metagento\Referrerurl\Controller\Adminhtml\Report;


class Customer extends
    \Metagento\Referrerurl\Controller\Adminhtml\AbstractController
{
    public function execute()
    {
        $page =  $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->prepend(__("Customer Referrer URL Report"));
        return $page;
    }

}