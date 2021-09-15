<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product\Type as ProductTypes;

class Base extends AbstractHelper
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    /**
     * Base constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->context      = $context;
        parent::__construct($context);
    }

    /**
     * @return int|null
     */
    public function getAdminCustomerId()
    {
        $customerId = $this->coreRegistry->registry('current_customer_id');

        if (is_null($customerId)) {
            $customerId = $this->context->getRequest()->getParam('id');
        }

        return $customerId;
    }

    /**
     * @return mixed
     */
    protected function getCurrentProduct()
    {
        return $this->coreRegistry->registry('current_product');
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->getCurrentProduct()->getId();
    }

    /**
     * @return int
     */
    public function getProductType()
    {
        return $this->getCurrentProduct()->getTypeId();
    }

    /**
     * @return array
     */
    public function getAllowedProductTypes()
    {
        return [ProductTypes::TYPE_SIMPLE, ProductTypes::TYPE_VIRTUAL, 'downloadable', 'grouped'];
    }
}