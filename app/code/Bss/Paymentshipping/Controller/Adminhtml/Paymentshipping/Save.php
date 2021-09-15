<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Paymentshipping
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Paymentshipping\Controller\Adminhtml\Paymentshipping;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var array
     */
    protected $_availableTypes = ['payment', 'shipping'];
    /**
     * @var string
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param \Bss\Paymentshipping\Model\PaymentshippingFactory $paymentShipping
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Bss\Paymentshipping\Model\PaymentshippingFactory $paymentShipping,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->myObjectManager = $context->getObjectManager();
        $this->paymentShipping = $paymentShipping;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Shipping action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $type = $this->getRequest()->getParam('type');
        if (!in_array($type, $this->_availableTypes)) {
            $this->messageManager->addError(__('Unable to save. Wrong type specified.'));
            $this->_redirect('*/*', ['type' => 'payment', '_current' => true]);
        }
        $websiteId = $this->getRequest()->getParam('website_id', 1);
        $methods  = $this->getRequest()->getPost('bssmethods');
        $methodCodes = $this->getRequest()->getPost('bssmethods_codes');

        foreach ($methodCodes as $methodCode) {
            $groups = isset($methods[$methodCode]) ? $methods[$methodCode] : [];
            $visibilitys = $this->paymentShipping->create()->getCollection();
            $visibilitys->addFieldToFilter('type', ['eq' => $type]);
            $visibilitys->addFieldToFilter('website_id', ['eq' =>$websiteId]);
            $visibilitys->addFieldToFilter('method', ['eq' =>$methodCode]);
            if ($visibilitys->getSize() > 0) {
                $firstItem = $visibilitys->getFirstItem();
                $id = ($firstItem) ? $firstItem->getEntityId() : null;
                if ($id) {
                    $modelUpdate = $this->loadModel($id);
                    $modelUpdate->setGroupIds(implode(',', $groups));
                    $this->saveModel($modelUpdate);
                }
            } else {
                $modelInsert = $this->getModel();
                $modelInsert->setType($type);
                $modelInsert->setWebsiteId($websiteId);
                $modelInsert->setMethod($methodCode);
                $modelInsert->setGroupIds(implode(',', $groups));
                $this->saveModel($modelInsert);
            }
        }
        $message = __('%1 options have been saved.', $type);
        $this->messageManager->addSuccess($message);

        $path = '*/*/' . $type . '/website_id/' . $websiteId;
        $resultRedirect->setPath($path);
        return $resultRedirect;
        
    }

    /**
     * @param $id
     * @return Single Model
     */
    protected function loadModel($id)
    {
        return $this->paymentShipping->create()->load($id);
    }

    /**
     * @return Model
     */
    protected function getModel()
    {
        return $this->paymentShipping->create();
    }

    /**
     * @param $model
     * @return this
     */
    protected function saveModel($model)
    {
        $model->save();
        return $this;
    }
}