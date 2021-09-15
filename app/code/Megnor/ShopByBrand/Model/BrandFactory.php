<?php
namespace Megnor\ShopByBrand\Model;
class BrandFactory
{
    protected $_objectManager;
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Megnor\ShopByBrand\Model\Items', $arguments, false);
    }
}