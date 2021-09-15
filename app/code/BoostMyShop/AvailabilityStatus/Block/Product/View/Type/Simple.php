<?php

namespace BoostMyShop\AvailabilityStatus\Block\Product\View\Type;

class Simple extends \Magento\Catalog\Block\Product\View\AbstractView
{
    protected $_availabilityStatus;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \BoostMyShop\AvailabilityStatus\Model\AvailabilityStatus $availabilityStatus,
        array $data = []
    ) {
        parent::__construct($context, $arrayUtils, $data);

        $this->_availabilityStatus = $availabilityStatus;
    }

    public function getMessage()
    {
        $result = $this->_availabilityStatus->getAvailability($this->getProduct(), $this->_storeManager->getStore()->getId());

        if ($result)
            return $result['message'];
    }

}