<?php
namespace Wanexo\Mlayer\Controller\Adminhtml\Banner;

use Magento\Framework\Registry;
use Wanexo\Mlayer\Controller\Adminhtml\Banner;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Wanexo\Mlayer\Model\BannerFactory;
use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Wanexo\Mlayer\Model\Banner\Image as ImageModel;
use Wanexo\Mlayer\Model\Banner\File as FileModel;
use Wanexo\Mlayer\Model\Upload;
use Magento\Backend\Helper\Js as JsHelper;

class Save extends Banner
{
    /**
     * banner factory
     * @var \Wanexo\Mlayer\Model\BannerFactory
     */
    protected $bannerFactory;

    /**
     * image model
     *
     * @var \Wanexo\Mlayer\Model\Banner\Image
     */
    protected $imageModel;

    /**
     * file model
     *
     * @var \Wanexo\Mlayer\Model\Banner\File
     */
    protected $fileModel;

    /**
     * upload model
     *
     * @var \Wanexo\Mlayer\Model\Upload
     */
    protected $uploadModel;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;


    /**
     * @param JsHelper $jsHelper
     * @param Session $backendSession
     * @param Date $dateFilter
     * @param ImageModel $imageModel
     * @param FileModel $fileModel
     * @param Upload $uploadModel
     * @param Registry $registry
     * @param BannerFactory $bannerFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        JsHelper $jsHelper,

        ImageModel $imageModel,
        FileModel $fileModel,
        Upload $uploadModel,
        Registry $registry,
        BannerFactory $bannerFactory,
        RedirectFactory $resultRedirectFactory,
        Date $dateFilter,
        Context $context
    )
    {
        $this->jsHelper = $jsHelper;
        $this->imageModel = $imageModel;
        $this->fileModel = $fileModel;
        $this->uploadModel = $uploadModel;
		$this->_modelBannerFactory = $bannerFactory;
        parent::__construct($registry, $bannerFactory, $resultRedirectFactory, $dateFilter, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('banner');
		
		if(isset($data['banner_image']['delete']) && !isset($_FILES['banner_image']['name']))
		{
			$om = \Magento\Framework\App\ObjectManager::getInstance();
			$filesystem = $om->get('Magento\Framework\Filesystem');
			$reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
			$AbsMediaPath =  $reader->getAbsolutePath();
			unlink($AbsMediaPath.'wanexo/mlayer/banner/image/'.$data['banner_image']['value']);
		}
		
		//die;
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->filterData($data);
            $banner = $this->initBanner();
            $banner->setData($data);
			if(isset($_FILES['banner_image']['name']) && $_FILES['banner_image']['name']!='') {
				if(isset($data['banner_image']['delete']))
				{
					$bannerImage = $this->uploadModel->deleteFileAndGetName('banner_image', $this->imageModel->getBaseDir(), $data);
				}
				else
				{
					$bannerImage = $this->uploadModel->uploadFileAndGetName('banner_image', $this->imageModel->getBaseDir(), $data);
				}
				
				$banner->setBannerImage($bannerImage);
			}
			else
			{
				if(isset($data['banner_id']))
				{
					if(isset($data['banner_image']['delete'])) {
						$banner->setBannerImage('');
					}
					else
					{
						$pdata =  $this->_modelBannerFactory->create()->load($data['banner_id']);
						$dbImage = $pdata['banner_image'];
						$banner->setBannerImage($dbImage);
					}
				}
			}
            $this->_eventManager->dispatch(
                'wanexo_mlayer_banner_prepare_save',
                [
                    'banner' => $banner,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $banner->save();
                $this->messageManager->addSuccess(__('The banner has been saved.'));
                $this->_getSession()->setWanexoMlayerBannerData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'wanexo_mlayer/*/edit',
                        [
                            'banner_id' => $banner->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('wanexo_mlayer/*/');
                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the banner.'));
            }

            $this->_getSession()->setWanexoMlayerBannerData($data);
            $resultRedirect->setPath(
                'wanexo_mlayer/*/edit',
                [
                    'banner_id' => $banner->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('wanexo_mlayer/*/');
        return $resultRedirect;
    }
}
