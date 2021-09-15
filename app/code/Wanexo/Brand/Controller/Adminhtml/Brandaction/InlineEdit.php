<?php
namespace Wanexo\Brand\Controller\Adminhtml\Brandaction;

use Magento\Backend\App\Action\Context;
use Wanexo\Brand\Model\BrandFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Wanexo\Brand\Controller\Adminhtml\Brand as BrandController;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Registry;
use Wanexo\Brand\Model\Brand;
use Magento\Framework\Stdlib\DateTime\Filter\Date;


class InlineEdit extends BrandController
{
    
    protected $jsonFactory;

    public function __construct(
        JsonFactory $jsonFactory,
        Registry $registry,
        BrandFactory $brandFactory,
        RedirectFactory $resultRedirectFactory,
      
        Context $context

    ) {
        $this->jsonFactory = $jsonFactory;
        parent::__construct($registry, $brandFactory, $resultRedirectFactory, $context);

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

        foreach (array_keys($postItems) as $brandId) {
           
            $brand = $this->brandFactory->create()->load($brandId);
            try {
                $brandData = $postItems[$brandId];
                $brand->addData($brandData);

                $brand->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithBrandId($brand, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithBrandId($brand, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithBrandId(
                    $brand,
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

    protected function getErrorWithBrandId(Brand $brand, $errorText)
    {
        return '[Brand ID: ' . $brand->getId() . '] ' . $errorText;
    }
}
