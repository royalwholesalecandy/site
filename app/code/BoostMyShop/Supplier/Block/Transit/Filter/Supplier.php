<?php

namespace BoostMyShop\Supplier\Block\Transit\Filter;

class Supplier extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    protected $_suppliersFactory;
    protected $_transitCollection;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Framework\DB\Helper $resourceHelper,
                                \BoostMyShop\Supplier\Model\ResourceModel\Supplier\CollectionFactory $suppliersFactory,
                                \BoostMyShop\Supplier\Model\ResourceModel\Transit\CollectionFactory $transitCollection,
                                array $data = [])
    {
        parent::__construct($context, $resourceHelper, $data);

        $this->_suppliersFactory = $suppliersFactory;
        $this->_transitCollection = $transitCollection;
    }

    /**
     * Get options
     *
     * @return array
     */
    protected function _getOptions()
    {
        $result = [];
        $result[] = ['value' => '', 'label' => ' '];
        foreach ($this->_suppliersFactory->create()->setOrder('sup_name', 'ASC') as $supplier) {
            $result[] = ['value' => $supplier->getId(), 'label' => $supplier->getsup_name()];
        }

        return $result;
    }

    /**
     * Get condition
     *
     * @return array|null
     */
    public function getCondition()
    {
        if ($this->getValue() === null) {
            return null;
        }

        $productIds = $this->_transitCollection->create()->init()->addSupplierFilter($this->getValue())->getAllProductIds();
        return ['in' => $productIds];
    }
}
