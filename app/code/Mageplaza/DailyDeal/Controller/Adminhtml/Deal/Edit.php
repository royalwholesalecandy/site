<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Controller\Adminhtml\Deal;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\DailyDeal\Controller\Adminhtml\Deal;
use Mageplaza\DailyDeal\Model\DealFactory;

/**
 * Class Edit
 * @package Mageplaza\DailyDeal\Controller\Adminhtml\Deal
 */
class Edit extends Deal
{
    /**
     * Page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Result JSON factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * Edit constructor.
     * @param Context $context
     * @param DealFactory $dealFactory
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        DealFactory $dealFactory,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;

        parent::__construct($context, $dealFactory, $coreRegistry);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $deal = $this->_initDeal();
        if ($this->getRequest()->getParam('id') && !$deal->getId()) {
            $this->messageManager->addErrorMessage(__('This Deal no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(
                '*/*/edit',
                [
                    'id'       => $deal->getId(),
                    '_current' => true
                ]
            );

            return $resultRedirect;
        }

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_DailyDeal::deal');
        $resultPage->getConfig()->getTitle()
            ->set(__('Daily Deal'))
            ->prepend($deal->getId() ? $deal->getProductName() : __('Create Deal'));

        return $resultPage;
    }
}