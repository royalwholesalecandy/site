<?php
namespace Wanexo\Mlayer\Controller\Adminhtml\Banner;

use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;
use Wanexo\Mlayer\Controller\Adminhtml\Banner;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Wanexo\Mlayer\Model\BannerFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Ui\Component\MassAction\Filter;
use Wanexo\Mlayer\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;
use Wanexo\Mlayer\Model\Banner as BannerModel;

abstract class MassAction extends Banner
{
    protected $filter;
    protected $collectionFactory;
    /**
     * @var string
     */
    protected $successMessage = 'Mass Action successful on %1 records';
    /**
     * @var string
     */
    protected $errorMessage = 'Mass Action failed';

    public function __construct(
        Filter $filter,
        BannerCollectionFactory $collectionFactory,
        Registry $registry,
        BannerFactory $bannerFactory,
        RedirectFactory $resultRedirectFactory,
        Date $dateFilter,
        Context $context
        
    ) {
        $this->filter = $filter;
        $this->bannerCollectionFactory = $collectionFactory;
        parent::__construct($registry, $bannerFactory, $resultRedirectFactory, $dateFilter, $context);
    }

   
    protected abstract function doTheAction(BannerModel $banner);

    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
       
        try { 
            $collection = $this->filter->getCollection($this->bannerCollectionFactory->create());
            
            $collectionSize = $collection->getSize();
            
            //die('st='.count($collection));
            foreach ($collection as $banner) {
                $this->doTheAction($banner);
            }
            $this->messageManager->addSuccess(__($this->successMessage, $collectionSize));
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($this->errorMessage));
        }
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('wanexo_mlayer/*/index');
        return $redirectResult;
    }
}