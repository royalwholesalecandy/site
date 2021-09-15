<?php
namespace BoostMyShop\AdvancedStock\Block\Routing;

class Index extends \Magento\Backend\Block\Template
{
    protected $_template = 'Routing/Index.phtml';

    protected $_coreRegistry = null;
    protected $_warehouseCollectionFactory;
    protected $_websiteCollectionFactory;
    protected $_storeGroupCollectionFactory;
    protected $_storeCollectionFactory;
    protected $_routingMode;
    protected $_router;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
                                \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
                                \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
                                \Magento\Store\Model\ResourceModel\Group\CollectionFactory $storeGroupCollectionFactory,
                                \BoostMyShop\AdvancedStock\Model\Routing\Store\Mode $routingMode,
                                \BoostMyShop\AdvancedStock\Model\Router $router,
                                \Magento\Framework\Registry $registry, array $data = [])
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_storeGroupCollectionFactory = $storeGroupCollectionFactory;
        $this->_storeCollectionFactory = $storeCollectionFactory;
        $this->_routingMode = $routingMode;
        $this->_router = $router;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/save');
    }

    public function getWarehouses()
    {
        return $this->_warehouseCollectionFactory->create();
    }

    public function getWebsites()
    {
        $websites = [];
        $websites[0] = "Global";

        foreach($this->_websiteCollectionFactory->create() as $website)
        {
            $websites[$website->getId()] = $website->getName();
        }

        return $websites;
    }

    public function getStoreGroups($websiteId)
    {
        $groups = [];
        $groups[0] = "";

        $collection = $this->_storeGroupCollectionFactory->create()->addFieldToFilter('website_id', $websiteId);

        foreach($collection as $group)
        {
            $groups[$group->getId()] = $group->getName();
        }

        return $groups;
    }

    public function getStores($groupId)
    {
        $stores = [];
        $stores[0] = "";

        $collection = $this->_storeCollectionFactory->create()->addFieldToFilter('group_id', $groupId);
        foreach($collection as $store)
        {
            $stores[$store->getId()] = $store->getName();
        }

        return $stores;
    }

    public function getParentName($websiteName, $groupName, $storeName)
    {
        $items = [];
        if ($websiteName)
            $items[] = $websiteName;
        if ($groupName)
            $items[] = $groupName;
        if ($storeName)
            $items[] = $storeName;

        array_pop($items);

        if (count($items) > 0)
            return implode(' > ', $items);
        else
            return "Global";
    }

    public function getYesNoDropDown($name, $value)
    {
        $html = '<select name="'.$name.'">';

        foreach([0 => 'No', 1 => 'Yes'] as $k => $v){
            $selected = ($k == $value ? ' selected ' : '');
            $html .= '<option  value="'.$k.'" '.$selected.'>'.$v.'</option>';
        }

        $html .= '</select>';

        return $html;
    }

    public function getPriorityDropDown($name, $value)
    {
        $html = '<select name="'.$name.'">';

        for($i=1;$i<100;$i++){
            $selected = ($i == $value ? ' selected ' : '');
            $html .= '<option  value="'.$i.'" '.$selected.'>'.$i.'</option>';
        }

        $html .= '</select>';

        return $html;
    }

    public function getRoutingModeDropDown($name, $id, $value, $visible)
    {
        $html = '<select name="'.$name.'"  id="'.$id.'" '.(!$visible ? 'style="display: none;"' : '').'>';

        foreach($this->_routingMode->toOptionArray() as $k => $v){
            $selected = ($k == $value ? ' selected ' : '');
            $html .= '<option  value="'.$k.'" '.$selected.'>'.__($v).'</option>';
        }

        $html .= '</select>';

        return $html;
    }

    public function getRoutingValue($websiteId, $groupId, $storeId, $field){
        $obj = $this->_router->getStoreConfiguration($websiteId, $groupId, $storeId);
        return $obj->getData($field);
    }

    public function getWarehouseValue($websiteId, $groupId, $storeId, $stockId, $field)
    {
        $obj = $this->_router->getStoreWarehouseConfiguration($websiteId, $groupId, $storeId, $stockId);
        return $obj->getData($field);
    }

}