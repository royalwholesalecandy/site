<?php

namespace Magewares\MWQuickOrder\Controller\Index;

class productdetails extends \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;
	
	protected $productloader;
	
	protected $productStatus;
	
	protected $productVisibility;
	
	protected $formKey;

    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Catalog\Model\ProductFactory $productloader,
		\Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
		\Magento\Catalog\Model\Product\Visibility $productVisibility,
		\Magento\Framework\Data\Form\FormKey $formKey

    ) {
        $this->resultJsonFactory = $resultJsonFactory;
		$this->productloader = $productloader;
		$this->productStatus = $productStatus;
		$this->productVisibility = $productVisibility;
		$this->formKey = $formKey;
        parent::__construct($context);
    }

    public function execute()
    {
		$pid = $this->getRequest()->getParam('pid');

		$product = $this->productloader->create()->load($pid);
			$resultArray['id'] = $pid;
			$resultArray['name'] = $product->getName();
			$resultArray['price'] = $product->getPrice();
			$resultArray['special_price'] = $product->getSpecialPrice();
			$resultArray['sku'] = $product->getSku();
			$resultArray['qty'] = 1;
			$resultArray['formkey'] = $this->formKey->getFormKey();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$listBlock = $objectManager->get('\Magento\Catalog\Block\Product\ListProduct');
			$addToCartUrl =  $listBlock->getAddToCartUrl($product);
			$resultArray['addtocartUrl'] = $addToCartUrl;
			$result = ['result' => $resultArray];
			$resultJson = $this->resultJsonFactory->create();
			return $resultJson->setData($result);
    }
}