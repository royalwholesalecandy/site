<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Product\Children;


class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{

    /**
     * Initialize select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinFields();
        return $this;
    }

    /**
     * Join fields to entity
     *
     * @return $this
     */
    protected function _joinFields()
    {
        $this->addAttributeToSelect('name');
        $this->addAttributeToSelect('sku');
        $this->addAttributeToSelect('status');

        $barcodeAttribute = $this->_scopeConfig->getValue('advancedstock/attributes/barcode_attribute');
        if ($barcodeAttribute)
            $this->addAttributeToSelect($barcodeAttribute);

        return $this;
    }

    public function addParentFilter($parentProduct)
    {
        switch($parentProduct->getTypeId())
        {
            case 'configurable':
                $this->addConfigurableProductParentFilter($parentProduct);
                break;
            case 'bundle':
                $this->addBundleProductParentFilter($parentProduct);
                break;
            case 'grouped':
                $this->addGroupedProductParentFilter($parentProduct);
                break;
            default:
                $this->addFieldToFilter('entity_id', -1);   //return nothing, should never happen
                break;
        }

        return $this;
    }

    protected function addConfigurableProductParentFilter($parentProduct)
    {
        $childrenIds = [];

        $collection = $parentProduct->getTypeInstance()->getUsedProducts($parentProduct);
        foreach($collection as $item)
            $childrenIds[] = $item->getId();

        $this->addFieldToFilter('entity_id', ['in' => $childrenIds]);
    }

    protected function addBundleProductParentFilter($parentProduct)
    {
        $childrenIds = [];

        $selectionCollection = $parentProduct->getTypeInstance(true)
            ->getSelectionsCollection(
                $parentProduct->getTypeInstance(true)->getOptionsIds($parentProduct),
                $parentProduct
            );

        foreach ($selectionCollection as $proselection)
            $childrenIds[] = $proselection->getProductId();

        $this->addFieldToFilter('entity_id', ['in' => $childrenIds]);
    }

    protected function addGroupedProductParentFilter($parentProduct)
    {
        $childrenIds = $parentProduct->getTypeInstance()->getChildrenIds($parentProduct->getId());
        $this->addFieldToFilter('entity_id', ['in' => $childrenIds]);
    }
}
