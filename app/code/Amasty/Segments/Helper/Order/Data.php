<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Helper\Order;

class Data extends \Amasty\Segments\Helper\Base
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context                      $context
     * @param \Magento\Framework\Registry                                $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                $date
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Eav\Model\Config                                  $eavConfig
     * @param \Magento\Framework\ObjectManagerInterface                  $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context, $coreRegistry, $date);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->objectManager = $objectManager;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getOrderAttribute($code)
    {
        try {
            $attribute = $this->objectManager
                ->create('Amasty\Orderattr\Model\ResourceModel\Eav\Attribute')
                ->loadOrderAttributeByCode($code);
        } catch (\ReflectionException $e) {
            try {
                $attribute = $this->objectManager
                    ->create('\Amasty\Orderattr\Model\Attribute\Repository')
                    ->get($code);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $attribute = null;
            }
        }

        return $attribute;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute[]
     */
    public function getOrderAttributes()
    {
        try {
            $attributeItems = $this->objectManager
                ->create('\Amasty\Orderattr\Model\AttributeMetadataDataProvider')
                ->loadAttributesCollection();
        } catch (\ReflectionException $e) {
            $attributeItems = $this->objectManager
                ->create('\Amasty\Orderattr\Model\ResourceModel\Attribute\Collection')
                ->setSortOrder()
                ->load();
        }

        return $attributeItems;
    }

    /**
     * @return array
     */
    public function getOrderAttributesForSource()
    {
        if (!$this->_moduleManager->isEnabled('Amasty_Orderattr')) {
            return [];
        }
        $attributes = $this->getOrderAttributes();

        $result = [];

        foreach ($attributes as $attribute) {

            if (!$attribute->getFrontendLabel()) {
                continue;
            }

            if (in_array($attribute->getFrontendInput(), ['file', 'image'])) {
                continue;
            }

            if (in_array($attribute->getAttributeCode(), ['default_billing', 'default_shipping'])) {
                continue;
            }

            $result[] = $attribute;
        }

        return $result;
    }

    /**
     * @param $customerEmail
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection|bool
     */
    public function getOrdersCollectionByCustomerEmail($customerEmail)
    {
        $orderCollection = $this->orderCollectionFactory
            ->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('customer_email', ['eq' => $customerEmail])
            ->addFieldToFilter('customer_is_guest', ['eq' => 1]);

        return $orderCollection->getSize() ? $orderCollection : false;
    }

    /**
     * @param $customerId
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection|bool
     */
    public function getOrdersCollectionByCustomerId($customerId)
    {
        $orderCollection = $this->orderCollectionFactory
            ->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);

        return $orderCollection->getSize() ? $orderCollection : false;
    }

    /**
     * @param \Magento\Customer\Model\Customer|\Amasty\Segments\Model\GuestCustomerData $customerModel
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection|bool
     */
    public function getCollectionByCustomerType($customerModel)
    {
        if (!$customerModel) {
            return false;
        }
        if ($customerModel->getCustomerIsGuest()) {
            $orders = $this->getOrdersCollectionByCustomerEmail($customerModel->getEmail());

            if (!$orders) {
                return false;
            }
        } else {
            $orders = $this->getOrdersCollectionByCustomerId($customerModel->getId());
        }

        return $orders;
    }
}
