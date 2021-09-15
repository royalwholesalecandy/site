<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog category image attribute backend model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Wanexo\Mdlnavi\Model\Category\Attribute\Backend;

class Image extends \Magento\Catalog\Model\Category\Attribute\Backend\Image
{
    const ADDITIONAL_DATA_PREFIX = '_additional_data_';
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     *
     * @deprecated
     */
    protected $_uploaderFactory;

    /**
     * Filesystem facade
     *
     * @var \Magento\Framework\Filesystem
     *
     * @deprecated
     */
    protected $_filesystem;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     *
     * @deprecated
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     *
     * @deprecated
     */
    protected $_logger;

    /**
     * Image uploader
     *
     * @var \Magento\Catalog\Model\ImageUploader
     */
    private $imageUploader;

    /**
     * Image constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     */
    private $objectManager;
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        //\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_logger = $logger;
        $this->objectManager = $objectManager;
    }
    
    /**
     * @param array $value Attribute value
     * @return string
     */
    protected function getUploadedImageName($value)
    {
        if (is_array($value) && isset($value[0]['name'])) {
            return $value[0]['name'];
        }

        return '';
     }
     
     /**

     * Avoiding saving potential upload data to DB
      *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $value = $object->getData($attributeName);

        if ($imageName = $this->getUploadedImageName($value)) {
            $object->setData(self::ADDITIONAL_DATA_PREFIX . $attributeName, $value);
            $object->setData($attributeName, $imageName);
        } else if (!is_string($value)) {
            $object->setData($attributeName, '');
        }

        return parent::beforeSave($object);
    }

    /**
     * Get image uploader
     *
     * @return \Magento\Catalog\Model\ImageUploader
     *
     * @deprecated
     */
    private function getImageUploader()
    {
        if ($this->imageUploader === null) {
           /* $this->imageUploader = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Catalog\CategoryImageUpload'
            );*/
            $this->imageUploader = $this->objectManager->get(\Magento\Catalog\CategoryImageUpload::class);
        }
        return $this->imageUploader;
    }

    /**
     * Save uploaded file and set its name to category
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Catalog\Model\Category\Attribute\Backend\Image
     */
    public function afterSave($object)
    {
        //$image = $object->getData($this->getAttribute()->getName(), null);
        $value = $object->getData(self::ADDITIONAL_DATA_PREFIX . $this->getAttribute()->getName());

        //if ($image !== null) {
            if ($imageName = $this->getUploadedImageName($value)) {
            try {
                //$this->getImageUploader()->moveFileFromTmp($image);
                $this->getImageUploader()->moveFileFromTmp($imageName);
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }
        return $this;
    }
}
