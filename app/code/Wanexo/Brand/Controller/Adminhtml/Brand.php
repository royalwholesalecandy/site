<?php
namespace Wanexo\Brand\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Wanexo\Brand\Model\BrandFactory;
use Magento\Framework\Registry;
//use Magento\Framework\Stdlib\DateTime\Filter\Date;

abstract class Brand extends Action
{
   
    protected $brandFactory;

    protected $coreRegistry;

   
    protected $resultRedirectFactory;

    public function __construct(
        Registry $registry,
        BrandFactory $brandFactory,
        RedirectFactory $resultRedirectFactory,
        //Date $dateFilter,
        Context $context,
         array $data = []

    )
    {
        $this->coreRegistry = $registry;
        $this->brandFactory = $brandFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        //$this->dateFilter = $dateFilter;
        parent::__construct($context , $data);
    }

    protected function initBrand()
    {
        $brandId  = (int) $this->getRequest()->getParam('brand_id');
        
        $brand    = $this->brandFactory->create();
        if ($brandId) {
            $brand->load($brandId);
        }
        $this->coreRegistry->register('wanexo_brand', $brand);
        return $brand;
    }

}
