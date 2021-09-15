<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wanexo\Mdlnavi\Model;

class Category extends \Magento\Catalog\Model\Category
{
    /**
     * Retrieve image URL
     *
     * @return string
     * @param string $attributeCode
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    //public function getImageUrl()
    public function getImageUrl($attributeCode = 'image')
    {
        $url = false;
        //$image = $this->getImage();
        $image = $this->getData($attributeCode);
        if ($image) {
            if (is_string($image)) {
                $url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . 'catalog/category/' . $image;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }
}
