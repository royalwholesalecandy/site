<?php
namespace Wanexo\Brand\Controller\Adminhtml\Brandaction;

use Wanexo\Brand\Controller\Adminhtml\Brand;

class Delete extends Brand
{ 
    public function execute()
    {
        $id = $this->getRequest()->getParam('brand_id');
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
        if ($id) {
            $name = "";
            try {
                $brand = $this->brandFactory->create();
                $brand->load($id);
                $name = $brand->getBrandTitle();
                $brand->delete();
                $this->messageManager->addSuccess(__('The brand has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_wanexo_brand_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('wanexo_brand/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_wanexo_brand_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('wanexo_brand/*/edit', ['brand_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a brand to delete.'));
        // go to grid
        $resultRedirect->setPath('wanexo_brand/*/');
        return $resultRedirect;
    }
}
