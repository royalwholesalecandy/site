<?php

namespace Magewares\MWQuickOrder\Controller\Index;

class searchProducts extends \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;
	
	protected $productCollectionFactory;
	
	protected $productStatus;
	
	protected $productVisibility;
	
	protected $stockFilter;

    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
		\Magento\Catalog\Model\Product\Visibility $productVisibility,
		\Magento\CatalogInventory\Helper\Stock $stockFilter

    ) {
        $this->resultJsonFactory = $resultJsonFactory;
		$this->productCollectionFactory = $productCollectionFactory;
		$this->productStatus = $productStatus;
		$this->productVisibility = $productVisibility;
		$this->stockFilter = $stockFilter; 
        parent::__construct($context);
    }

    public function execute()
    {
		$searchquery = $this->getRequest()->getParam('querystring');

		if(!empty($searchquery)){
		    $collection = $this->productCollectionFactory->create();
			$collection->addAttributeToSelect('*');
			$collection->addAttributeToFilter(
				array(
					array('attribute'=> 'name','like' => '%'.$searchquery.'%'),
					array('attribute'=> 'sku','like' => '%'.$searchquery.'%'),
					)
			);
			$collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
			$collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
			
			//check for instock products only
			$this->stockFilter->addInStockFilterToCollection($collection);
			
			$searchProductsArr= array();
			$html="<div class='search-products'><ul>";
			if($collection->getSize()>0){
				foreach($collection->getData() as $key => $product){

					$searchProductsArr[$product['entity_id']]=array(
							'name' => $product['name'],
							'sku' =>  $product['sku'],
							'type' => $product['type_id']
					);
					$html.="<li><div class='productdetails' id='".$product['entity_id']."' data-type='".$product['type_id']."'>".$product['name']."</div></li>";				
				}
			}else{
				$html.="There is no product related to search";
			}
			$html.="</ul></div>";
			$result = ['result' => $html];
			$resultJson = $this->resultJsonFactory->create();
			return $resultJson->setData($result);

		}
        
    }
}