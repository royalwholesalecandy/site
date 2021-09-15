<?php
namespace Wanexo\Mlayer\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action\Context;
use Wanexo\Mlayer\Model\BannerFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Wanexo\Mlayer\Controller\Adminhtml\Banner as BannerController;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Registry;
use Wanexo\Mlayer\Model\Banner;
use Magento\Framework\Stdlib\DateTime\Filter\Date;


class InlineEdit extends BannerController
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @param JsonFactory $jsonFactory
     * @param Registry $registry
     * @param BannerFactory $bannerFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Registry $registry,
        BannerFactory $bannerFactory,
        RedirectFactory $resultRedirectFactory,
        Date $dateFilter,
        Context $context

    ) {
        $this->jsonFactory = $jsonFactory;
        parent::__construct($registry, $bannerFactory, $resultRedirectFactory, $dateFilter, $context);

    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $bannerId) {
            /** @var \Wanexo\Mlayer\Model\Banner $banner */
            $banner = $this->bannerFactory->create()->load($bannerId);
            try {
                $bannerData = $this->filterData($postItems[$bannerId]);
                $banner->addData($bannerData);

                $banner->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithBannerId($banner, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithBannerId($banner, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithBannerId(
                    $banner,
                    __('Something went wrong while saving the page.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add banner id to error message
     *
     * @param Banner $banner
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithBannerId(Banner $banner, $errorText)
    {
        return '[Banner ID: ' . $banner->getId() . '] ' . $errorText;
    }
}
