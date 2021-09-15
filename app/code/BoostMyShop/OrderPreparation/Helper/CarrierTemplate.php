<?php

namespace BoostMyShop\OrderPreparation\Helper;


class CarrierTemplate
{
    protected $_templateCollectionFactory;

    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate\CollectionFactory $templateCollectionFactory){
        $this->_templateCollectionFactory = $templateCollectionFactory;
    }

    public function getCarrierTemplateForOrder($orderInProgress)
    {
        $shippingMethod = $orderInProgress->getOrder()->getShippingMethod();

        if ($shippingMethod)
        {
            $template = $this->_templateCollectionFactory->create()->addActiveFilter()->addShippingMethodFilter($shippingMethod)->getFirstItem();
            if ($template->getId())
                return $template;
        }

        return false;
    }

}