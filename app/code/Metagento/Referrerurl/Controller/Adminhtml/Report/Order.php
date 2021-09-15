<?php


namespace Metagento\Referrerurl\Controller\Adminhtml\Report;


class Order extends
    \Metagento\Referrerurl\Controller\Adminhtml\AbstractController
{
    public function execute()
    {
        $page =  $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->prepend(__("Order Referrer URL Report"));
        return $page;
    }

}