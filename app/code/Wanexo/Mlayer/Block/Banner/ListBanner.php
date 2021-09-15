<?php
namespace Wanexo\Mlayer\Block\Banner;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlFactory;
use Wanexo\Mlayer\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;

/**
 * @method \Wanexo\Mlayer\Model\ResourceModel\Banner\Collection getBanners()
 * @method ListBanner setBanners(\Wanexo\Mlayer\Model\ResourceModel\Banner\Collection $banners)
 */
class ListBanner extends Template
{
    /**
     * @var BannerCollectionFactory
     */
    protected $bannerCollectionFactory;
    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @param BannerCollectionFactory $bannerCollectionFactory
     * @param UrlFactory $urlFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        BannerCollectionFactory $bannerCollectionFactory,
        UrlFactory $urlFactory,
        Context $context,
        array $data = []
    )
    {
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->urlFactory = $urlFactory;
        parent::__construct($context, $data);
    }

    /**
     * load the banners
     */
    protected  function _construct()
    {
        parent::_construct();
        /** @var \Wanexo\Mlayer\Model\ResourceModel\Banner\Collection $banners */
        $banners = $this->bannerCollectionFactory->create()->addFieldToSelect('*')
            ->addFieldToFilter('is_active', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('title', 'ASC');
			//print_r($banners);
        $this->setBanners($banners);
    }

    /**
     * @return $this
     */
   /* protected function _prepareLayout()
    {
        parent::_prepareLayout();
       
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'wanexo_mlayer.banner.list.pager');
        $pager->setCollection($this->getBanners());
        $this->setChild('pager', $pager);
        $this->getBanners()->load();
        return $this;
    }*/

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
	
	public function getMediaUrl()
	{
		$currentStore = $this->_storeManager->getStore();
		$mediaPath = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		return $mediaPath;
	}
}
