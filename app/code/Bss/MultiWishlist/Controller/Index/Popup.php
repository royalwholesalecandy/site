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
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Popup extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Popup constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
    }

    public function execute()
    {

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->customerSession->isLoggedIn()) {
            $this->customerSession->setAfterAuthUrl($this->_url->getCurrentUrl());
            $result['url'] = $this->_url->getUrl('customer/account/login');
            $this->getResponse()->setBody(json_encode($result));
            return;
        }
        $params = $this->getRequest()->getParams();
        $action = isset($params['action'])? $params['action'] : false;
        $unwishlist = isset($params['wishlist_id'])? $params['wishlist_id'] : false;
        $layout = $this->_view->getLayout();
        $template = 'Bss_MultiWishlist::popup.phtml';
        $result = $this->_view->getLayout()
                              ->createBlock('Bss\MultiWishlist\Block\Popup', '', ['data' => ['action' => $action,'unwishlist' => $unwishlist]])
                              ->setTemplate($template)->toHtml();
        $this->getResponse()->setBody(json_encode($result));
        return;
    }

}

