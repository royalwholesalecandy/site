<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Akeans\ShowPriceAfterLogin\Controller\Index;

class Index extends \Magento\Newsletter\Controller\Manage
{
    /**
     * Managing newsletter subscription page
     *
     * @return void
     */
    public function execute()
    {
		//echo 'test';
        $this->_view->loadLayout();
		//print_r($this->_view->getPage()->getConfig());die;
        if ($block = $this->_view->getLayout()->getBlock('customer_newsletter')) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Newsletter Subscription'));
        $this->_view->renderLayout();
    }
}
