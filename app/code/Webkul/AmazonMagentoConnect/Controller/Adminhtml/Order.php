<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Order extends Action
{

    /**
     * @param Context     $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context
    ) {
    
        parent::__construct($context);
    }

    public function execute()
    {
    }
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization
                    ->isAllowed(
                        'Webkul_AmazonMagentoConnect::Order'
                    );
    }
}
