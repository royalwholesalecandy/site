<?php

namespace BoostMyShop\OrderPreparation\Model\Pdf;

use Magento\Framework\App\Filesystem\DirectoryList;

class Barcode
{
    protected $_filesystem;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_filesystem = $filesystem;
    }

    /**
     * Return a GDI barcode image
     * @param $barcode
     * @return mixed
     */
    public function createBarcodeImage($barcode)
    {
        $barcodeStandard = 'Code128';
        $barcodeOptions = array('text' => $barcode);
        $rendererOptions = array();
        $factory = \Zend_Barcode::factory($barcodeStandard, 'image', $barcodeOptions, $rendererOptions);
        $image = $factory->draw();
        return $image;
    }

    /**
     * Return a zend pdf image
     *
     * @param $barcode
     */
    public function getZendPdfBarcodeImage($barcode)
    {
        $tempImage = $this->createBarcodeImage($barcode);

        if (!is_dir($this->_filesystem->getDirectoryWrite(DirectoryList::TMP)->getAbsolutePath()))
            mkdir($this->_filesystem->getDirectoryWrite(DirectoryList::TMP)->getAbsolutePath());
        $tempPath = $this->_filesystem->getDirectoryWrite(DirectoryList::TMP)->getAbsolutePath('bms_orderpreparation_barcodelabel.png');
        imagepng($tempImage, $tempPath);
        $zendPicture = \Zend_Pdf_Image::imageWithPath($tempPath);
        unlink($tempPath);
        return $zendPicture;
    }

}