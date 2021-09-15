<?php
namespace Wanexo\Mlayer\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Wanexo\Mlayer\Model\BannerFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

abstract class Banner extends Action
{
    /**
     * banner factory
     *
     * @var BannerFactory
     */
    protected $bannerFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * date filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @param Registry $registry
     * @param BannerFactory $bannerFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        BannerFactory $bannerFactory,
        RedirectFactory $resultRedirectFactory,
        Date $dateFilter,
        Context $context

    )
    {
        $this->coreRegistry = $registry;
        $this->bannerFactory = $bannerFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->dateFilter = $dateFilter;
        parent::__construct($context);
    }

    /**
     * @return \Wanexo\Mlayer\Model\Banner
     */
    protected function initBanner()
    {
        $bannerId  = (int) $this->getRequest()->getParam('banner_id');
        /** @var \Wanexo\Mlayer\Model\Banner $banner */
        $banner    = $this->bannerFactory->create();
        if ($bannerId) {
            $banner->load($bannerId);
        }
        $this->coreRegistry->register('wanexo_mlayer_banner', $banner);
        return $banner;
    }

    /**
     * filter dates
     *
     * @param array $data
     * @return array
     */
    public function filterData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            ['dob' => $this->dateFilter],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        return $data;
    }

}
