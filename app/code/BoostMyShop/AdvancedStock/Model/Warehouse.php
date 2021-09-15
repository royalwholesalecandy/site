<?php

namespace BoostMyShop\AdvancedStock\Model;


class Warehouse extends \Magento\Framework\Model\AbstractModel
{
    protected $_productsFactory = null;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse');
    }

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Product\AllFactory $productFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_productsFactory = $productFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getFullAddress($html = false)
    {
        $address = [];
        $address[] = $this->getw_company_name();
        $address[] = $this->getw_street1();
        if ($this->getw_street2())
            $address[] = $this->getw_street2();
        $address[] = $this->getw_city().', '.$this->getw_postcode();
        $address[] = $this->getw_state();
        $address[] = $this->getw_country();
        if ($this->getw_telephone())
            $address[] = $this->getw_telephone();

        return implode(($html ? '<br>' : "\n"), $address);
    }

    public function applyDefaultValues()
    {
        $this->setw_is_active(1);
        return $this;
    }


    public function getAddress($html = false)
    {
        $separator = ($html ? '<br>' : "\n");

        $address = [];
        $address[] = $this->getw_company_name();
        $address[] = $this->getData('w_street1');
        $address[] = $this->getwData('w_street2');
        $address[] = $this->getw_postcode().' '.$this->getw_city();
        $address[] = $this->getw_state();
        $address[] = $this->getw_country();
        $address[] = $this->getw_telephone();

        return implode($separator, $address);
    }

    public function getTotalValue()
    {
        return $this->_productsFactory->create()->addWarehouseFilter($this->getId())->getTotalValue();
    }

    public function getSkuCount()
    {
        return $this->_getResource()->getSkuCount($this->getId());
    }

    public function getProductsCount()
    {
        return $this->_getResource()->getProductsCount($this->getId());
    }

}
