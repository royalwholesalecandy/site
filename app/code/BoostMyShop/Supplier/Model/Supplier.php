<?php

namespace BoostMyShop\Supplier\Model;


class Supplier extends \Magento\Framework\Model\AbstractModel
{

    protected $_currencyFactory;
    protected $_currency;
    protected $_supplierProductFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\Supplier\Model\Supplier\ProductFactory $supplierProductFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_currencyFactory = $currencyFactory;
        $this->_supplierProductFactory = $supplierProductFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\ResourceModel\Supplier');
    }

    /**
     * Apply default values for fields
     *
     * @return $this
     */
    public function applyDefaultData()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');

        $this->setsup_is_active(1);
        $this->setsup_locale($config->getValue('general/locale/code'));
        $this->setsup_currency($config->getValue('currency/options/base'));
        $this->setsup_country($config->getValue('general/country/default'));

        return $this;
    }

    public function getCurrency()
    {
        if (!$this->_currency)
            $this->_currency = $this->_currencyFactory->create()->load($this->getsup_currency());
        return $this->_currency;
    }

    public function isAssociatedToProduct($productId)
    {
        $obj = $this->_supplierProductFactory->create()->loadByProductSupplier($productId, $this->getId());
        return ($obj->getId() > 0 ? true : false);
    }

    /**
     * @param $productId
     */
    public function associateProduct($productId)
    {
        $obj = $this->_supplierProductFactory->create();
        $obj->setsp_product_id($productId);
        $obj->setsp_sup_id($this->getId());
        $obj->save();

        return $obj;
    }

    public function removeProduct($productId)
    {
        $obj = $this->_supplierProductFactory->create()->loadByProductSupplier($productId, $this->getId());
        $obj->delete();

        return $this;
    }

    public function getLeadTime()
    {
        return ((int)$this->getsup_supply_delay()) + ((int)$this->getsup_shipping_delay());
    }

}
