<?php
/**
 * Copyright © 2015 Megnor. All rights reserved.
 */

namespace Megnor\ShopByBrand\Controller\Adminhtml\Items;

class Index extends \Megnor\ShopByBrand\Controller\Adminhtml\Items
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('megnor::base');
        $resultPage->getConfig()->getTitle()->prepend(__('Megnor ShopByBrand'));
        $resultPage->addBreadcrumb(__('Megnor'), __('Megnor'));
        $resultPage->addBreadcrumb(__('Items'), __('ShopByBrand'));
        return $resultPage;
    }
}
