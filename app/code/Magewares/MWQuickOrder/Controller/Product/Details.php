<?php

namespace Magewares\MWQuickOrder\Controller\Product;

class Details extends \Magento\Framework\App\Action\Action
{
	protected $resultJsonFactory;
	
	protected $productloader;
	
	protected $formKey;
	
	public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Catalog\Model\ProductFactory $productloader,
		\Magento\Framework\Data\Form\FormKey $formKey
	){
		$this->resultJsonFactory = $resultJsonFactory;
		$this->productloader = $productloader;
		$this->formKey = $formKey;
		parent::__construct($context);
	}	
	
    public function execute()
    {
		$product_type = $this->getRequest()->getParam('type');
		if($product_type == 'configurable'){
			//in configurable product
			$pid = $this->getRequest()->getParam('pid');
			$childSelected = $this->getRequest()->getParam('childSelected');
			if(!empty($childSelected)){
				$childSelected = $this->getRequest()->getParam('childSelected');
				if(!empty($pid)){
					$product = $this->productloader->create()->load($pid);
					$resultArray['qty'] = 1;
					$children = $product->getTypeInstance()->getUsedProducts($product);
					foreach ($children as $child){
						if($child->getData('entity_id') == $childSelected){
							$resultArray['id'] = $pid;
							$resultArray['name'] = $child->getData('name');
							$resultArray['price'] = $child->getData('price');
							$resultArray['sku'] = $child->getData('sku');
							$resultArray['special_price'] = $child->getData('special_price');
						}
					}
				}
			}else{
				$attrcode = $this->getRequest()->getParam('attrcode');
				$attrId = $this->getRequest()->getParam('attrId');
				$optionSelected = $this->getRequest()->getParam('optionSelected');
				if(!empty($pid)){
					$attrcode=explode(',',$attrcode);
					$attrId=explode(',',$attrId);
					$optionSelected=explode(',',$optionSelected);
					$product = $this->productloader->create()->load($pid);
					$resultArray['qty'] = 1;
					$children = $product->getTypeInstance()->getUsedProducts($product);
					foreach ($children as $child){
						if($child->getData($attrcode[0]) == $optionSelected[0] && 
							$child->getData($attrcode[1]) == $optionSelected[1]){
							$resultArray['id'] = $pid;
							$resultArray['name'] = $child->getData('name');
							$resultArray['price'] = $child->getData('price');
							$resultArray['sku'] = $child->getData('sku');
							$resultArray['special_price'] = $child->getData('special_price');
						} 
					}
				}
			}
			$result = ['result' => $resultArray];
			$resultJson = $this->resultJsonFactory->create();
		}else if($product_type == 'bundle'){
			$items = $this->getRequest()->getParam('items');
			$pid = $this->getRequest()->getParam('pid');
			$price = $this->getRequest()->getParam('bundleprice');
			//$options = explode(',',$this->getRequest()->getParam('options'));
			//$selection = explode(',',$this->getRequest()->getParam('selection'));
			//$qty_arr = explode(',',$this->getRequest()->getParam('qty_arr'));
			$resultArray=array();
			if(!empty($pid) && !empty($items)){
				$product = $this->productloader->create()->load($pid);
				$resultArray['id'] = $product->getData('entity_id');
				$resultArray['name'] = $product->getData('name');
				$resultArray['price'] = $price;
				$resultArray['sku'] = $product->getData('sku');
				$resultArray['qty'] = 1;
				$resultArray['special_price'] = $product->getData('special_price');
				//create options list
				$ItemsArr=explode(',',$items);
				$html='<div class="bundle-items-wrapper"><b style="width:100%">Bundle Items:</b>';
				foreach($ItemsArr as $item){
				$html.= '<span class="mwbundle-child-item">'.$item .'</span>';
				}
				$html.='<div>';
				$resultArray['items'] = $html;
				/* if(!empty($options) && !empty($selection)){
					for($i=0;$i<sizeof($options);$i++){
					$params[$options[$i]] = $selection[$i];
					$qtyArr[$options[$i]] = $qty_arr[$i];
					}
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$jsonHelper = $objectManager->create('\Magento\Framework\Json\Helper\Data');
					$resultArray['selection'] = $jsonHelper->jsonEncode($params);
					$resultArray['qtyArr'] = $jsonHelper->jsonEncode($qtyArr);
				} */
			}
			$result = ['result' => $resultArray];
			$resultJson = $this->resultJsonFactory->create();
		}else if($product_type == 'grouped'){
		//in grouped products
		$products = $this->getRequest()->getParam('products');
		$qty = $this->getRequest()->getParam('qty');
			if(!empty($products) && !empty($qty)){
				$products=explode(',',$products);
				$qty=explode(',',$qty);
				$resultArray=array();
				for($i=0; $i < sizeof($products); $i++){
				$productId=$products[$i];
				$product = $this->productloader->create()->load($productId);
				$resultArray[$i]['id'] = $product->getData('entity_id');
				$resultArray[$i]['name'] = $product->getData('name');
				$resultArray[$i]['price'] = $product->getData('price');
				$resultArray[$i]['sku'] = $product->getData('sku');
				$resultArray[$i]['special_price'] = $product->getData('special_price');
				$resultArray[$i]['qty'] = $qty[$i];
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$listBlock = $objectManager->get('\Magento\Catalog\Block\Product\ListProduct');
				$addToCartUrl =  $listBlock->getAddToCartUrl($product);
				$resultArray[$i]['addtocartUrl'] = $addToCartUrl;
				$resultArray[$i]['formkey'] = $this->formKey->getFormKey();
				}
			}
			$result = ['result' => $resultArray];
			$resultJson = $this->resultJsonFactory->create();
		}
		return $resultJson->setData($result);
	}
	
}