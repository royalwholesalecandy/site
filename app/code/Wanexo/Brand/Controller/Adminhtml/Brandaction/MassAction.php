<?php
namespace Wanexo\Brand\Controller\Adminhtml\Brandaction;

use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;
use Wanexo\Brand\Controller\Adminhtml\Brand;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Wanexo\Brand\Model\BrandFactory;
//use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Ui\Component\MassAction\Filter;
use Wanexo\Brand\Model\ResourceModel\Brand\CollectionFactory as BrandCollectionFactory;
use Wanexo\Brand\Model\Brand as BrandModel;

abstract class MassAction extends Brand
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
        BrandCollectionFactory $collectionFactory,
        Registry $registry,
        BrandFactory $authorFactory,
        RedirectFactory $resultRedirectFactory,
        //Date $dateFilter,
        Context $context
        
    ) {
        $this->filter = $filter;
        $this->brandCollectionFactory = $collectionFactory;
        parent::__construct($registry, $authorFactory, $resultRedirectFactory, $context);
    }

   
    protected abstract function doTheAction(BrandModel $author);

    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {

        try {
            $collection = $this->filter->getCollection($this->brandCollectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $author) {
                $this->doTheAction($author);
            }
            $this->messageManager->addSuccess(__($this->successMessage, $collectionSize));
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($this->errorMessage));
        }
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('wanexo_brand/*/index');
        return $redirectResult;
    }
}
