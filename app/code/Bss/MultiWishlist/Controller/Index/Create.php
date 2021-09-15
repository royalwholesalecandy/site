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

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session as CustomerSession;
use Bss\MultiWishlist\Helper\Data as Helper;
use Bss\MultiWishlist\Model\WishlistLabel;

class Create extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $validatorFormKey;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var WishlistLabel
     */
    protected $wishlistLabel;

    /**
     * Create constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Data\Form\FormKey\Validator $validatorFormKey
     * @param CustomerSession $customerSession
     * @param Helper $helper
     * @param WishlistLabel $wishlistLabel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $validatorFormKey,
        CustomerSession $customerSession,
        Helper $helper,
        WishlistLabel $wishlistLabel
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->context = $context;
        $this->validatorFormKey = $validatorFormKey;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->wishlistLabel = $wishlistLabel;
    }

    /**
     * MultiWishlist create wishlist group
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->customerSession->setAfterAuthUrl($this->_url->getCurrentUrl());
            $this->customerSession->authenticate();
        }
        $post = $this->getRequest()->getPost();
        $name = $post['new_wlname'];
        $param = $this->getRequest()->getParams();
        $model = $this->wishlistLabel;
        $response = [];
        $layout = $this->_view->getLayout();
        $template = 'Bss_MultiWishlist::popup.phtml';
        $customer = $this->customerSession->getCustomer();
        $collection = $this->helper->getWishlistCollection();
        $collection = $collection->addFieldToFilter('wishlist_name', $name)
            ->addFieldToFilter('customer_id', $customer->getId());
        $result = $collection->getFirstItem();
        if ($result->getId() || strtolower($name) == 'main') {
            if (!isset($param['ajax'])) {
                $this->messageManager->addErrorMessage(__('Already exist a Wishlist. Please choose a different name.'));
            } else {
                $response['error'] = "<div class='message-success success message'><div data-bind='html: message.text'>".__('Already exist a Wishlist. Please choose a different name.').".</div></div>";
                $response['html'] = '';
            }
        } else {
            $model->setCustomerId($customer->getId());
            $model->setWishlistName($name);
            $model->save();
            
            if (!isset($param['ajax'])) {
                $this->messageManager->addSuccessMessage(__('Successfully saved the wishlist.'));
            } else {
                $response['success'] = "<div class='message-success success message'><div data-bind='html: message.text'>".__('Successfully saved the wishlist.').".</div></div>";
                $response['html'] = $layout->createBlock('Bss\MultiWishlist\Block\Popup')
                                ->setTemplate($template)
                                ->setAction(false)
                                ->toHtml();
            }
           
        }

        if (isset($param['ajax'])) {
            $this->getResponse()->setBody(json_encode($response));
        } else {
            $this->_redirect('wishlist');
        }
    }
}
