<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wanexo\Mdlnavi\Model\Category;

use Magento\Catalog\Model\Category\Attribute\Backend\Image as ImageBackendModel;

/**
 * Class DataProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $category = $this->getCurrentCategory();
        if ($category) {
            $categoryData = $category->getData();
            $categoryData = $this->addUseDefaultSettings($category, $categoryData);
            $categoryData = $this->addUseConfigSettings($categoryData);
            $categoryData = $this->filterFields($categoryData);
            /*if (isset($categoryData['image'])) {
                unset($categoryData['image']);
                $categoryData['image'][0]['name'] = $category->getData('image');
                $categoryData['image'][0]['url'] = $category->getImageUrl();
            }*/
            $categoryData = $this->convertValues($category, $categoryData);
            $this->loadedData[$category->getId()] = $categoryData;
        }
        return $this->loadedData;
    }

    
    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param array $categoryData
     * @return array
     */
    private function convertValues($category, $categoryData)
    {
        foreach ($category->getAttributes() as $attributeCode => $attribute) {
            if (!isset($categoryData[$attributeCode])) {
                continue;
            }

            if ($attribute->getBackend() instanceof ImageBackendModel) {
                unset($categoryData[$attributeCode]);

                $categoryData[$attributeCode][0]['name'] = $category->getData($attributeCode);
                $categoryData[$attributeCode][0]['url'] = $category->getImageUrl($attributeCode);
            }
        }

        return $categoryData;
    }
}