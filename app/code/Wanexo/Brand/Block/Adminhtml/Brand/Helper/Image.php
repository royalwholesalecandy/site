<?php
namespace Wanexo\Brand\Block\Adminhtml\Brand\Helper;

use Magento\Framework\Data\Form\Element\Image as ImageField;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Escaper;
use Wanexo\Brand\Model\Brand\Image as BrandImage;
use Magento\Framework\UrlInterface;


class Image extends ImageField
{
    
    protected $imageModel;

    public function __construct(
        BrandImage $imageModel,
        ElementFactory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        UrlInterface $urlBuilder,
        $data = []
    )
    {
        $this->imageModel = $imageModel;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $urlBuilder, $data);
    }

    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = $this->imageModel->getBaseUrl().$this->getValue();
        }
        return $url;
    }
}