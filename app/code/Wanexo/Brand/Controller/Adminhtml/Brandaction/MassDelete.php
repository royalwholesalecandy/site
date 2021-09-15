<?php
namespace Wanexo\Brand\Controller\Adminhtml\Brandaction;

use Wanexo\Brand\Model\Brand;

class MassDelete extends MassAction
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 record(s) have been deleted';
    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while deleting record(s).';

    /**
     * @param $author
     * @return $this
     */

    protected function doTheAction(Brand $brand)
    {
		
		$id = $brand["brand_id"];
        $resultRedirect = $this->resultRedirectFactory->create();
		
		$object1 = $this->_objectManager;
		$attr = $object1->create('Wanexo\Brand\Block\BrandBlock');
		$brandCollection = $attr->getBrand()->addFieldToFilter('brand_id',$id);
		  
		   foreach($brandCollection as $raw)
		   {
			$optionName = strtolower($raw->getBrandOptionName());
		   }
		$object2 = $this->_objectManager;
			$attr = $object2->create('\Magento\Eav\Model\Entity\Attribute');
			$attributeId = $attr->getIdByCode('catalog_product', 'is_brand');
		$object3 = $this->_objectManager;
		    $optionCollection = $object3->create('\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection')
		    ->setAttributeFilter($attributeId)
            ->setPositionOrder('asc', true)
            ->load();
			foreach($optionCollection as $option)
			{
			    $optionValue = strtolower($option["value"]);
				 if($optionName == $optionValue)
			  {
					$option->delete();
			  }
			}
        $brand->delete();
        return $this;
    }
}
