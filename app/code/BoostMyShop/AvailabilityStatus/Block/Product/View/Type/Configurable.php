<?php

namespace BoostMyShop\AvailabilityStatus\Block\Product\View\Type;

class Configurable extends \Magento\Catalog\Block\Product\View\AbstractView
{
    protected $_availabilityStatus;
    protected $_availabilities;

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
        if (!$this->getProduct()->isAvailable())
            return $this->getOosMessage();
        else
            return $this->getBestChildMessage();
    }

    protected function getChildProducts()
    {
        return $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct());
    }

    protected function getOosMessage()
    {
        $result = $this->_availabilityStatus->getOutOfStockMessage($this->getProduct(), $this->_storeManager->getStore()->getId());
        if ($result)
            return $result['message'];
    }

    protected function getBestChildMessage()
    {
        $soonestProductId = null;
        foreach($this->getAvailabilities() as $productId => $availability)
        {
            if (!$soonestProductId || ($availability['date'] < $this->getAvailabilityForProductId($soonestProductId)['date']))
                $soonestProductId = $productId;
        }

        if ($soonestProductId)
            return $this->getAvailabilityForProductId($soonestProductId)['message'];
    }

    protected function getAvailabilityForProductId($productId)
    {
        return $this->getAvailabilities()[$productId];
    }

    public function getAvailabilities()
    {
        if (!$this->_availabilities)
        {
            $this->_availabilities = [];
            foreach($this->getChildProducts() as $child)
            {
                $childAvailability = $this->_availabilityStatus->getAvailability($child, $this->_storeManager->getStore()->getId());
                $this->_availabilities[$child->getId()] = $childAvailability;
            }
        }
        return $this->_availabilities;
    }

}