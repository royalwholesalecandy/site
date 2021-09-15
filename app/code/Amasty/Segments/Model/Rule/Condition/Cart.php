<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition;

use Magento\Quote\Api\CartRepositoryInterface;

class Cart extends \Amasty\Segments\Model\Rule\Condition\Condition
{
    /**
     * use MainValidation trait
     */
    use \Amasty\Segments\Traits\MainValidation, \Amasty\Segments\Traits\DayValidation;

    /**
     * @var \Amasty\Segments\Helper\Base
     */
    protected $helper;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartManagement;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * Cart constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Amasty\Segments\Helper\Base          $baseHelper
     * @param CartRepositoryInterface               $cartManagement
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Amasty\Segments\Helper\Base $baseHelper,
        CartRepositoryInterface $cartManagement,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper                 = $baseHelper;
        $this->cartManagement         = $cartManagement;
        $this->logger                 = $context->getLogger();
        $this->quoteCollectionFactory = $quoteCollection;
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'created_at'       => __('Days From Cart Created'),
            'updated_at'       => __('Days From Cart Modified'),
            'base_grand_total' => __('Grand Total'),
            'items_qty'        => __('Products Count'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'created_at':
            case 'updated_at':
                return 'day';
        }

        return 'numeric';
    }

    /**
     * @param \Magento\Customer\Model\Customer|\Amasty\Segments\Model\GuestCustomerData $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $model = $this->objectValidation($model);

        if (!$model) {
            return false;
        }

        try {
            if ($model->getCustomerIsGuest()) {
                $quote = $model;
            } else {
                $quote = $this->quoteCollectionFactory->create()
                    ->addFieldToFilter('customer_id', $model->getId())
                    ->setOrder('updated_at', \Magento\Quote\Model\ResourceModel\Quote\Collection::SORT_ORDER_DESC)
                    ->setPageSize(1)
                    ->getFirstItem();
            }

            if ($model && $quote->getId()) {
                if ($this->getInputType() == 'day') {
                    $attributeValue = $this->prepareDayValidation($quote);

                    return parent::validateAttribute($attributeValue);
                }

                return parent::validate($quote);
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function canValidateGuest()
    {
        return true;
    }
}
