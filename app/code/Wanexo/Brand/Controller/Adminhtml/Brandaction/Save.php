<?php
namespace Wanexo\Brand\Controller\Adminhtml\Brandaction;

use Magento\Framework\Registry;
use Wanexo\Brand\Controller\Adminhtml\Brand;
//use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Wanexo\Brand\Model\BrandFactory;
use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Wanexo\Brand\Model\Brand\Image as ImageModel;
use Wanexo\Brand\Model\Upload;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface; 
use Magento\Framework\Filesystem;

class Save extends Brand
{
    /**
     * author factory
     * @var \Sample\News\Model\AuthorFactory
     */
    protected $brandFactory;

    /**
     * image model
     *
     * @var \Sample\News\Model\Author\Image
     */
    protected $imageModel;

    /**
     * file model
     *
     * @var \Sample\News\Model\Author\File
     */
    protected $fileModel;

    /**
     * upload model
     *
     * @var \Sample\News\Model\Upload
     */
    protected $uploadModel;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;
   
    protected $scopeConfig;
	
    public function __construct(
		ScopeConfigInterface $scopeConfig,
		AdapterFactory $imageFactory,
		StoreManagerInterface $storeManager,
		Filesystem $filesystem,
        JsHelper $jsHelper,
        ImageModel $imageModel,
        //FileModel $fileModel,
        Upload $uploadModel,
        Registry $registry,
        BrandFactory $brandFactory,
        RedirectFactory $resultRedirectFactory,
       // Date $dateFilter,
        Context $context
    )
    {
        $this->jsHelper = $jsHelper;
        $this->imageModel = $imageModel;
      //  $this->fileModel = $fileModel;
        $this->uploadModel = $uploadModel;
		$this->_scopeConfig = $scopeConfig;
		$this->_imageFactory = $imageFactory;
		$this->_filesystem = $filesystem;
 
        $this->_storeManager = $storeManager;
 
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
 
        parent::__construct($registry, $brandFactory, $resultRedirectFactory, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
		$data = $this->getRequest()->getPost('brand');
           //print_r($data);die;
		$resultRedirect = $this->resultRedirectFactory->create();
		$object = $this->_objectManager;
		$attr = $object->create('Wanexo\Brand\Block\BrandBlock');
		$brandCollection = $attr->getBrand();
		 
		$brandOptionName = strtolower($data["brand_option_name"]);
		     $brandTitle = strtolower($data["brand_title"]);
		//echo $data["brand_id"];die;
		
		if(!isset($data["brand_id"]))
		{
			    foreach($brandCollection as $item)
			{
				$optionname[]  = strtolower($item->getBrandOptionName());
				$title[]  = strtolower($item->getBrandTitle());
				 if(in_array($brandOptionName,$optionname))
				{
					$this->messageManager->addSuccess(__('The brand option name already exist.'));
                    $this->_getSession()->setWanexoBrandData(false);
					$resultRedirect->setPath('wanexo_brand/*/new');
					return $resultRedirect; 
				}
				elseif(in_array($brandTitle,$title))
				{
				    $this->messageManager->addSuccess(__('The brand title already exist.'));
                    $this->_getSession()->setWanexoBrandData(false);
					$resultRedirect->setPath('wanexo_brand/*/new');
					return $resultRedirect; 	
				}
			}
		}	
		 $store = $data['stores'][0];
        
        if ($data) {
            //$data = $this->filterData($data);
            $brand = $this->initBrand();
            $brand->setData($data);
            $brandimage = $this->uploadModel->uploadFileAndGetName('brand_image', $this->imageModel->getBaseDir(), $data);
			 //print_r($brandimage.$thumbimage);die;
			$thumbimage = $this->uploadModel->uploadFileAndGetName('brand_thumbimage',$this->imageModel->getBaseDir(), $data);
			$resizedPath = $this->imageModel->getBaseDir();
			// echo $resizedPath;die;
			$resize = $resizedPath."thumb";
			$imagefolder = "wanexo/brand/brand/image/";
			$thumbfolder = "wanexo/brand/brand/image/thumb/";
			 //echo $resize;die;
			 if(!is_dir($resize))
			{
				mkdir($resize, 0777,true);
				//chmod($resize, 0777,true); 
			}
		   if ($this->_scopeConfig->getValue('brand_section/home_settings/resizeimage',\Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 1)
		   {
                      
			$imgHeight = $this->_scopeConfig->getValue('brand_section/home_settings/thumbheight',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $imgWidth = $this->_scopeConfig->getValue('brand_section/home_settings/thumbwidth',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			  
			             $height = ($imgHeight > 0) ? $imgHeight : 200;
						  $width = ($imgWidth > 0) ? $imgWidth : 200; 
						 
							$value = explode('.',$thumbimage);
						if($value[1])
						{
			                $imageName =  $value[0].'_thumb.'.$value[1];
							$absPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagefolder).$thumbimage;
							$imageResized = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($thumbfolder).$imageName;
							//echo $imageResized;die;
							$imageResize = $this->_imageFactory->create();
							$imageResize->open($absPath);
 
							$imageResize->constrainOnly(TRUE);
							 
							$imageResize->keepTransparency(TRUE);
							 
							$imageResize->keepFrame(FALSE);
							 
							$imageResize->keepAspectRatio(true);
							 
							$imageResize->resize($width,$height);
							 
							$dest = $imageResized ;
							 
							$imageResize->save($dest);
						}
			   
			}
		if(!isset($data["brand_id"]))
		{
			$object = $this->_objectManager;
			$attr = $object->create('\Magento\Eav\Model\Entity\Attribute');
			$attributeId = $attr->getIdByCode('catalog_product', 'is_brand');
			$attr->load($attributeId);
			//echo $attr->getValue();die;
			$option = [];
			$option['value']['value'][0] = $data["brand_option_name"];
			 //print_r($value);die;
			$attr->addData(array('option' => $option));
			$attr->save();
			//echo $attr->getId(); die;
		}
		if(isset($data["brand_id"]))
		{
			$objectManager = $this->_objectManager;
			 $product = $objectManager->create('\Magento\Eav\Model\Entity\Attribute');
			 $attribut = $product->getIdByCode('catalog_product', 'is_brand');
			 $product->load($attribut);
			    $collection = $objectManager->create('Wanexo\Brand\Block\BrandBlock');
		        $brandCollection = $collection->getBrand()->addFieldToFilter('brand_id',$data["brand_id"]);;
					foreach($brandCollection as $raw)
					{
						$optionName = strtolower($raw->getBrandOptionName());
					}
				$model = $objectManager->create('Magento\Catalog\Model\Product');
	            $attribute = $model->getResource()->getAttribute("is_brand");
				$option_id = $attribute->getSource()->getOptionId($optionName);
				
            $updateOption = [];
			$updateOption['value'][$option_id][0] = $data["brand_option_name"];
			$product->addData(array('option' => $updateOption));
			$product->save();
		}
            $brand->setBrandImage($brandimage);
			$brand->setBrandThumbimage($thumbimage);
			//$brand->setOptionId($option_id);
			$brand->setStoreId($store);
            $this->_eventManager->dispatch(
                'wanexo_brand_prepare_save',
                [
                    'brand' => $brand,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $brand->save();
				if(!isset($data["brand_id"]))
				{
					$obManager = $this->_objectManager;
					$AttributeModel = $obManager->create('\Magento\Eav\Model\Entity\Attribute');
					$attribute = $AttributeModel->getIdByCode('catalog_product', 'is_brand');
					$AttributeModel->load($attribute);
					$Brandcollections = $obManager->create('Wanexo\Brand\Block\BrandBlock');
					$brandsCollection = $Brandcollections->getBrand()->addFieldToFilter('brand_id',$brand->getId());
						foreach($brandsCollection as $brand)
						{
							$brandOptionName = strtolower($brand->getBrandOptionName());
						}
					$productModel = $obManager->create('Magento\Catalog\Model\Product');
					$attribute = $productModel->getResource()->getAttribute("is_brand");
					$attrOption_id = $attribute->getSource()->getOptionId($brandOptionName);
					
					//$brandsLoadCollection = $Brandcollections->getBrand()->load($brand->getId())->setOptionId($attrOption_id)->save();
					$loadBrandModel = $obManager->create('Wanexo\Brand\Model\Brand')->load($brand->getId())->setOptionId($attrOption_id)->save();
					//$attribute = $AttributeModel->getIdByCode('catalog_product', 'is_brand');
					//$AttributeModel->load($attribute);
					
					
				}
                $this->messageManager->addSuccess(__('The brand has been saved.'));
                $this->_getSession()->setWanexoBrandData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'wanexo_brand/*/edit',
                        [
                            'brand_id' => $brand->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('wanexo_brand/*/');
                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the brand.'));
            }

            $this->_getSession()->setWanexoBrandData($data);
            $resultRedirect->setPath(
                'wanexo_brand/*/edit',
                [
                    'brand_id' => $brand->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('wanexo_brand/*/');
        return $resultRedirect;
    }
}
