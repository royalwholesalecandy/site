<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\PriceRule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\PriceRule;

class AddRule extends PriceRule
{
     /**
      * @var \Magento\Framework\View\Result\PageFactory
      */
    private $resultPageFactory;

    /**
     * object of ResultFactory
     * @var Resultfactory
     */
    protected $resultFactory;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
    
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Create badge
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
       /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('edit');
        return $resultForward;
    }
}
