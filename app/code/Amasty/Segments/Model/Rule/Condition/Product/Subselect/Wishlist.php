<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition\Product\Subselect;

class Wishlist extends \Amasty\Segments\Model\Rule\Condition\Product\Subselect
{
    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * Subselect constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        array $data = []
    ) {
        parent::__construct($context, $conditionProduct, $data);
        $this->setType(\Amasty\Segments\Helper\Condition\Data::AMASTY_SEGMENTS_PATH_TO_CONDITIONS
            . 'Product\Subselect\Wishlist')->setValue(null);
        $this->type = 'wishlist';
        $this->wishlistFactory = $wishlistFactory;
    }

    /**
     * @param $customer
     * @return \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    public function getValidationCollection($customer)
    {
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customer->getId());

        return $wishlist->getItemCollection();
    }
}
