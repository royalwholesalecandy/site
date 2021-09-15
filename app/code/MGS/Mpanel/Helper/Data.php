<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Mpanel\Helper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Contact base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_storeManager;
	
	protected $_date;
	
	protected $_url;
	
	protected $_filesystem;
	
	protected $_request;
	
	protected $_acceptToUsePanel = false;
	
	protected $_useBuilder = false;
	
	protected $_customer;
	
	/**
     * Asset service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;
	
    protected $filterManager;
	
	/**
     * Block factory
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;
	
	/**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;
	
	protected $_file;
	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    protected $_fullActionName;
	
    protected $_currentCategory;
	
    protected $_currentProduct;
	
    protected $scopeConfig;
	
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\Url $url,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\View\Element\Context $context,
		\Magento\Cms\Model\BlockFactory $blockFactory,
		\Magento\Cms\Model\PageFactory $pageFactory,
		\Magento\Framework\Filesystem\Driver\File $file,
		CustomerSession $customerSession,
		\Magento\Catalog\Model\Category $category,
                \Magento\Catalog\Model\Design $catalogDesign
	) {
		$this->scopeConfig = $context->getScopeConfig();
		$this->_storeManager = $storeManager;
		$this->_date = $date;
		$this->_url = $url;
		$this->_filesystem = $filesystem;
		$this->customerSession = $customerSession;
		$this->_objectManager = $objectManager;
		$this->_request = $request;
		$this->filterManager = $context->getFilterManager();
		$this->_assetRepo = $context->getAssetRepository();
		$this->_blockFactory = $blockFactory;
		$this->_pageFactory = $pageFactory;
		$this->_file = $file;
		$this->_category = $category;
                $this->_catalogDesign = $catalogDesign;
		
		$this->_fullActionName = $this->_request->getFullActionName();
		
		if($this->_fullActionName == 'catalog_category_view'){
			$this->_currentCategory = $this->getCurrentCategory();
		}
		
		if($this->_fullActionName == 'catalog_product_view'){
			$this->_currentProduct = $this->getCurrentProduct();
		}
	}
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
	
	/**
     * Retrieve current url in base64 encoding
     *
     * @return string
     */
	public function getCurrentBase64Url()
    {
		return strtr(base64_encode($this->_url->getCurrentUrl()), '+/=', '-_,');
    }
	
	/**
     * base64_decode() for URLs decoding
     *
     * @param    string $url
     * @return   string
     */
    public function decode($url)
    {
        $url = base64_decode(strtr($url, '-_,', '+/='));
        return $this->_url->sessionUrlVar($url);
    }

    /**
     * Returns customer id from session
     *
     * @return int|null
     */
    public function getCustomerId()
    {
		$customerInSession = $this->_objectManager->create('Magento\Customer\Model\Session');
        return $customerInSession->getCustomerId();
    }
	
	/* Get current customer */
	public function getCustomer(){
		if(!$this->_customer){
			$this->_customer = $this->getModel('Magento\Customer\Model\Customer')->load($this->getCustomerId());
		}
		return $this->_customer;
	}
	
	public function getStore(){
		return $this->_storeManager->getStore();
	}
	
	/* Get system store config */
	public function getStoreConfig($node, $storeId = NULL){
		if($storeId != NULL){
			return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
		}
		return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
	}
	
	// Check to accept to use builder panel
    public function acceptToUsePanel() {
		if($this->_acceptToUsePanel){
			return true;
		}else{
			if ($this->showButton() && ($this->customerSession->getUsePanel() == 1)) {
				$this->_acceptToUsePanel = true;
				return true;
			}
			$this->_acceptToUsePanel = false;
			return false;
		}
        
    }

	/* Check to visible panel button */
    public function showButton() {

        if ($this->getStoreConfig('mpanel/general/is_enabled')) {
            $customer = $this->getCustomer();
			if($customer->getIsBuilderAccount() == 1){
				return true;
			}
			return false;
        }

        return false;
    }
	
	/* Get all settings of the theme */
	public function getThemeSettings(){
		return [
			'catalog'=> 
			[
				'per_row' => $this->getStoreConfig('mpanel/catalog/product_per_row'),
				'featured' => $this->getStoreConfig('mpanel/catalog/featured'),
				'hot' => $this->getStoreConfig('mpanel/catalog/hot'),
				'ratio' => $this->getStoreConfig('mpanel/catalog/picture_ratio'),
				'new_label' => $this->getStoreConfig('mpanel/catalog/new_label'),
				'sale_label' => $this->getStoreConfig('mpanel/catalog/sale_label'),
				'preload' => $this->getStoreConfig('mpanel/catalog/preload'),
				'wishlist_button' => $this->getStoreConfig('mpanel/catalog/wishlist_button'),
				'compare_button' => $this->getStoreConfig('mpanel/catalog/compare_button'),
                                'layout_product'=> $this->getStoreConfig('mpanel/catalog/layout_product')
			],
			'catalogsearch'=> 
			[
				'per_row' => $this->getStoreConfig('mpanel/catalogsearch/product_per_row')
			],
			'product_details'=> 
			[
				'sku' => $this->getStoreConfig('mpanel/product_details/sku'),
				'reviews_summary' => $this->getStoreConfig('mpanel/product_details/reviews_summary'),
				'wishlist' => $this->getStoreConfig('mpanel/product_details/wishlist'),
				'compare' => $this->getStoreConfig('mpanel/product_details/compare'),
				'preload' => $this->getStoreConfig('mpanel/product_details/preload'),
				'short_description' => $this->getStoreConfig('mpanel/product_details/short_description'),
				'upsell_products' => $this->getStoreConfig('mpanel/product_details/upsell_products')
			],
			'product_tabs'=> 
			[
				'show_description' => $this->getStoreConfig('mpanel/product_tabs/show_description'),
				'show_additional' => $this->getStoreConfig('mpanel/product_tabs/show_additional'),
				'show_reviews' => $this->getStoreConfig('mpanel/product_tabs/show_reviews'),
				'show_product_tag_list' => $this->getStoreConfig('mpanel/product_tabs/show_product_tag_list')
			],
			'contact_google_map'=> 
			[
				'display_google_map' => $this->getStoreConfig('mpanel/contact_google_map/display_google_map'),
				'address_google_map' => $this->getStoreConfig('mpanel/contact_google_map/address_google_map'),
				'html_google_map' => $this->getStoreConfig('mpanel/contact_google_map/html_google_map'),
				'pin_google_map' => $this->getStoreConfig('mpanel/contact_google_map/pin_google_map'),
				'api_key_google_map' => $this->getStoreConfig('mpanel/contact_google_map/api_key_google_map')
			],
			'banner_slider'=> 
			[
				'slider_tyle' => $this->getStoreConfig('mgstheme/banner_slider/slider_tyle'),
				'id_reslider' => $this->getStoreConfig('mgstheme/banner_slider/id_reslider'),
				'identifier_block' => $this->getStoreConfig('mgstheme/banner_slider/identifier_block'),
				'banner_owl_auto' => $this->getStoreConfig('mgstheme/banner_slider/banner_owl_auto'),
				'banner_owl_speed' => $this->getStoreConfig('mgstheme/banner_slider/banner_owl_speed'),
				'banner_owl_loop' => $this->getStoreConfig('mgstheme/banner_slider/banner_owl_loop'),
				'banner_owl_nav' => $this->getStoreConfig('mgstheme/banner_slider/banner_owl_nav'),
				'banner_owl_dot' => $this->getStoreConfig('mgstheme/banner_slider/banner_owl_dot')
			],
			'blog'=> 
			[
				'blog_layout' => $this->getStoreConfig('mgstheme/blog/blog_layout'),
				'blog_cols' => $this->getStoreConfig('mgstheme/blog/blog_cols')
			]
		];
	}
	
	/* Get col for responsive */
	public function getColClass($perrow = NULL){
		if(!$perrow){
			$settings = $this->getThemeSettings();
			$perrow = $settings['catalog']['per_row'];
			
			if($this->_fullActionName == 'catalog_category_view'){
				$category = $this->_currentCategory;
				$categoryPerrow = $category->getPerRow();
				if($categoryPerrow!=''){
					$perrow = $categoryPerrow;
				}
			}
			
			if($this->_fullActionName == 'catalogsearch_result_index'){
				$perrow = $settings['catalogsearch']['per_row'];
			}
			
		}
		
		switch($perrow){
			case 2:
				return 'col-lg-6 col-md-6 col-sm-6 col-xs-6';
				break;
			case 3:
				return 'col-lg-4 col-md-4 col-sm-4 col-xs-6';
				break;
			case 4:
				return 'col-lg-3 col-md-3 col-sm-4 col-xs-6';
				break;
			case 5:
				return 'col-lg-custom-5 col-md-custom-5 col-sm-4 col-xs-6';
				break;
			case 6:
				return 'col-lg-2 col-md-2 col-sm-4 col-xs-6';
				break;
			case 7:
				return 'col-lg-custom-7 col-md-custom-7 col-sm-4 col-xs-6';
				break;
			case 8:
				return 'col-lg-custom-8 col-md-custom-8 col-sm-4 col-xs-6';
				break;
		}
		return;
	}
	/* Get class clear left */
	public function getClearClass($perrow = NULL, $nb_item){
		if(!$perrow){
			$settings = $this->getThemeSettings();
			$perrow = $settings['catalog']['per_row'];
                        
                        if($this->_fullActionName == 'catalog_category_view'){
				$category = $this->_currentCategory;
				$categoryPerrow = $category->getPerRow();
				if($categoryPerrow !=''){
					$perrow = $categoryPerrow;
				}
			}
			
			if($this->_fullActionName == 'catalogsearch_result_index'){
				$perrow = $settings['catalogsearch']['per_row'];
			}
		}
		//echo $perrow;
		
		$clearClass = '';
		switch($perrow){
			case 2:
				if($nb_item % 2 == 1){
					$clearClass.= " first-row-item row-sm-first row-xs-first";
				}
				return $clearClass;
				break;
			case 3:
				if($nb_item % 3 == 1){
					$clearClass.= " first-row-item row-sm-first";
				}
				if($nb_item % 2 == 1){
					$clearClass.= " row-xs-first";
				}
				return $clearClass;
				break;
			case 4:
				if($nb_item % 4 == 1){
					$clearClass.= " first-row-item";
				}
				if($nb_item % 3 == 1){
					$clearClass.= " row-sm-first";
				}
				if($nb_item % 2 == 1){
					$clearClass.= " row-xs-first";
				}
				return $clearClass;
				break;
            case 5:
				if($nb_item % 5 == 1){
					$clearClass.= " first-row-item";
				}
				if($nb_item % 3 == 1){
					$clearClass.= " row-sm-first";
				}
				if($nb_item % 2 == 1){
					$clearClass.= " row-xs-first";
				}
				return $clearClass;
				break;
			case 6:
				if($nb_item % 6 == 1){
					$clearClass.= " first-row-item";
				}
				if($nb_item % 3 == 1){
					$clearClass.= " row-sm-first";
				}
				if($nb_item % 2 == 1){
					$clearClass.= " row-xs-first";
				}
				return $clearClass;
				break;
            case 7:
				if($nb_item % 7 == 1){
					$clearClass.= " first-row-item";
				}
				if($nb_item % 3 == 1){
					$clearClass.= " row-sm-first";
				}
				if($nb_item % 2 == 1){
					$clearClass.= " row-xs-first";
				}
				return $clearClass;
				break;
            case 8:
				if($nb_item % 8 == 1){
					$clearClass.= " first-row-item";
				}
				if($nb_item % 3 == 1){
					$clearClass.= " row-sm-first";
				}
				if($nb_item % 2 == 1){
					$clearClass.= " row-xs-first";
				}
				return $clearClass;
				break;
		}
		return $clearClass;
	}
	
	/* Get product image size */
	public function getImageSize($ratio = NULL){
		if(!$ratio){
			$ratio = $this->getStoreConfig('mpanel/catalog/picture_ratio');
			if($this->_fullActionName == 'catalog_category_view'){
				$category = $this->_currentCategory;
				$categoryRatio = $category->getPictureRatio();
				if($categoryRatio!=''){
					$ratio = $categoryRatio;
				}
			}
		}
		
		$maxWidth = $this->getStoreConfig('mpanel/catalog/max_width_image');
		$result = [];
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth));
                break;
            // 1/2 Portrait
            case 2:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth*2));
                break;
            // 2/3 Portrait
            case 3:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth * 1.5)));
                break;
            // 3/4 Portrait
            case 4:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth * 4) / 3));
                break;
            // 2/1 Landscape
            case 5:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth/2));
                break;
            // 3/2 Landscape
            case 6:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth*2) / 3));
                break;
            // 4/3 Landscape
            case 7:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth*3) / 4));
                break;
        }

        return $result;
	}
	
	/* Get product image size for product details page*/
	public function getImageSizeForDetails() {
		$ratio = $this->getStoreConfig('mpanel/catalog/picture_ratio');
		$maxWidth = $this->getStoreConfig('mpanel/catalog/max_width_image_detail');
        $result = [];
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth));
                break;
            // 1/2 Portrait
            case 2:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth*2));
                break;
            // 2/3 Portrait
            case 3:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth * 1.5)));
                break;
            // 3/4 Portrait
            case 4:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth * 4) / 3));
                break;
            // 2/1 Landscape
            case 5:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth/2));
                break;
            // 3/2 Landscape
            case 6:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth*2) / 3));
                break;
            // 4/3 Landscape
            case 7:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth*3) / 4));
                break;
        }

        return $result;
    }
	
	public function getImageMinSize() {
        $ratio = $this->getStoreConfig('mpanel/catalog/picture_ratio');
        $result = [];
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = array('width' => 80, 'height' => 80);
                break;
            // 1/2 Portrait
            case 2:
                $result = array('width' => 80, 'height' => 160);
                break;
            // 2/3 Portrait
            case 3:
                $result = array('width' => 80, 'height' => 120);
                break;
            // 3/4 Portrait
            case 4:
                $result = array('width' => 80, 'height' => 107);
                break;
            // 2/1 Landscape
            case 5:
                $result = array('width' => 80, 'height' => 40);
                break;
            // 3/2 Landscape
            case 6:
                $result = array('width' => 80, 'height' => 53);
                break;
            // 4/3 Landscape
            case 7:
                $result = array('width' => 80, 'height' => 60);
                break;
        }

        return $result;
    }
	
	public function getProductLabel($product){
		$html = '';
		$newLabel = $this->getStoreConfig('mpanel/catalog/new_label');
        $saleLabel = $this->getStoreConfig('mpanel/catalog/sale_label');

		// Sale label
		$price = $product->getPrice();
		$finalPrice = $product->getFinalPrice();
		if(($finalPrice<$price) && ($saleLabel!='')){
			$html .= '<span class="product-label sale-label"><span>'.$saleLabel.'</span></span>';
		}
		
		// New label
		$now = $this->_date->gmtDate();
		$dateTimeFormat = \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT;
		$newFromDate = $product->getNewsFromDate();
        $newFromDate = date($dateTimeFormat, strtotime($newFromDate));
        $newToDate = $product->getNewsToDate();
        $newToDate = date($dateTimeFormat, strtotime($newToDate));
		if ((!(empty($newToDate) && empty($newFromDate)) && ($newFromDate < $now || empty($newFromDate)) && ($newToDate > $now || empty($newToDate)) && ($newLabel != '')) || ((empty($newToDate) && ($newFromDate < $now)) && ($newLabel != ''))) {
			$html.='<span class="product-label new-label"><span>'.$newLabel.'</span></span>';
		}
		
		return $html;
	}
	
	public function getUrlBuilder(){
		return $this->_url;
	}
	
	public function getCssUrl(){
		return $this->_url->getUrl('mpanel/index/css',['store'=>$this->getStore()->getId()]);
	}
	
	public function getPanelCssUrl(){
		return $this->_url->getUrl('mpanel/index/panelstyle');
	}
	
	public function getFonts() {
        return [
            ['css-name' => 'Lato', 'font-name' => __('Lato')],
            ['css-name' => 'Open+Sans', 'font-name' => __('Open Sans')],
            ['css-name' => 'Roboto', 'font-name' => __('Roboto')],
            ['css-name' => 'Roboto Slab', 'font-name' => __('Roboto Slab')],
            ['css-name' => 'Oswald', 'font-name' => __('Oswald')],
            ['css-name' => 'Source+Sans+Pro', 'font-name' => __('Source Sans Pro')],
            ['css-name' => 'PT+Sans', 'font-name' => __('PT Sans')],
            ['css-name' => 'PT+Serif', 'font-name' => __('PT Serif')],
            ['css-name' => 'Droid+Serif', 'font-name' => __('Droid Serif')],
            ['css-name' => 'Josefin+Slab', 'font-name' => __('Josefin Slab')],
            ['css-name' => 'Montserrat', 'font-name' => __('Montserrat')],
            ['css-name' => 'Ubuntu', 'font-name' => __('Ubuntu')],
            ['css-name' => 'Titillium+Web', 'font-name' => __('Titillium Web')],
            ['css-name' => 'Noto+Sans', 'font-name' => __('Noto Sans')],
            ['css-name' => 'Lora', 'font-name' => __('Lora')],
            ['css-name' => 'Playfair+Display', 'font-name' => __('Playfair Display')],
            ['css-name' => 'Bree+Serif', 'font-name' => __('Bree Serif')],
            ['css-name' => 'Vollkorn', 'font-name' => __('Vollkorn')],
            ['css-name' => 'Alegreya', 'font-name' => __('Alegreya')],
            ['css-name' => 'Noto+Serif', 'font-name' => __('Noto Serif')]
        ];
    }
	
	public function getLinksFont() {
        $setting = [
			'default_font' => $this->getStoreConfig('mgstheme/fonts/default_font'),
			'h1' => $this->getStoreConfig('mgstheme/fonts/h1'),
			'h2' => $this->getStoreConfig('mgstheme/fonts/h2'),
			'h3' => $this->getStoreConfig('mgstheme/fonts/h3'),
			'h4' => $this->getStoreConfig('mgstheme/fonts/h4'),
			'h5' => $this->getStoreConfig('mgstheme/fonts/h5'),
			'h6' => $this->getStoreConfig('mgstheme/fonts/h6'),
			'price' => $this->getStoreConfig('mgstheme/fonts/price'),
			'menu' => $this->getStoreConfig('mgstheme/fonts/menu'),
		];
        $fonts = [];
        $fonts[] = $setting['default_font'];

        if (!in_array($setting['h1'], $fonts)) {
            $fonts[] = $setting['h1'];
        }

        if (!in_array($setting['h2'], $fonts)) {
            $fonts[] = $setting['h2'];
        }

        if (!in_array($setting['h3'], $fonts)) {
            $fonts[] = $setting['h3'];
        }

        if (!in_array($setting['price'], $fonts)) {
            $fonts[] = $setting['price'];
        }

        if (!in_array($setting['menu'], $fonts)) {
            $fonts[] = $setting['menu'];
        }

        $fonts = array_filter($fonts);
        $links = '';

        foreach ($fonts as $_font) {
			$links .= '<link href="//fonts.googleapis.com/css?family=' . $_font . ':400,300,300italic,400italic,700,700italic,900,900italic" rel="stylesheet" type="text/css"/>';
        }

        return $links;
    }
	
	// get theme color
    public function getThemecolorSetting($storeId) {
        $setting = [
            '.modal-popup .modal-footer .action-secondary:hover, .modal-popup .modal-footer .action-primary, .btn-default:hover,.btn-default:active,.btn-default:focus, .btn-primary, .btn-primary.disabled,.btn-primary[disabled],fieldset[disabled] .btn-primary,.btn-primary.disabled:hover,.btn-primary[disabled]:hover,fieldset[disabled] .btn-primary:hover,.btn-primary.disabled:focus,.btn-primary[disabled]:focus,fieldset[disabled] .btn-primary:focus,.btn-primary.disabled:active,.btn-primary[disabled]:active,fieldset[disabled] .btn-primary:active,.btn-primary.disabled.active,.btn-primary.active[disabled],fieldset[disabled] .btn-primary.active,.btn.disabled,.btn[disabled],fieldset[disabled] .btn, .btn-secondary:hover,.btn-secondary:focus,.btn-secondary:active, .slider_mgs_carousel.owl-carousel .owl-dots .owl-dot.active span,.slider_mgs_carousel.owl-carousel .owl-dots .owl-dot:hover span, .block-cart-header .showcart .box-shopbag .icon-cart .count, .header1 .box-social-header .block-social-header ul li a:hover,.header5 .box-social-header .block-social-header ul li a:hover,.header6 .box-social-header .block-social-header ul li a:hover, .header1 .box-social-header .block-social-header ul li:hover a,.header5 .box-social-header .block-social-header ul li:hover a,.header6 .box-social-header .block-social-header ul li:hover a, .header5 .top-bar-abs .navigation .nav-main > li > a:hover:before, .block-social ul li a:hover, .scroll-to-top:hover, .title-block p.h4:before,.title-block p.h4:after, .block-tab-products .nav-tabs li.active a,.block-tab-products .nav-tabs li a:hover, .btn-addto:hover,.btn-addto:focus,.btn-addto:active, .btn-addto-default:hover,.btn-addto-default:active,.btn-addto-default:focus, .list-products .product-items .product-item .product-top .product-item-inner .btn-cart, .organie3-block-life .item-block:hover [class*="icon-"], .block-shop-by-cate .button-cate:hover,.block-shop-by-cate .button-cate:focus,.block-shop-by-cate .button-cate:active, .modes .modes-mode.active,.modes .modes-mode:hover,.modes .modes-mode:active, .pages .pagination .item a:focus, .pages .pagination .item span:focus, .pages .pagination .item.current a,.pages .pagination .item:hover a, .products-list .product-item .product-item-details .product-item-inner .product-item-actions .btn-addto:hover, .products-list .product-item:hover .product-item-details .product-item-name h4:after, .product-details-view .product-info-main .product-add-form .product-addto .btn-addto:hover,.product-details-view .product-info-main .product-add-form .product-addto .btn-addto:focus,.product-details-view .product-info-main .product-add-form .product-addto .btn-addto:active, .bundle-options-container .block-bundle-summary .bundle-info .product-details .product-addto .btn-addto:hover, .block.related .block-actions button.select:hover,.block.related .block-actions button.select:focus,.block.related .block-actions button.select:active, #shopping-cart-table tbody tr td .actions-toolbar > .action-delete:hover, .cart-container .shopping-cart-bottom .discount .content .coupon .actions-toolbar button.action:hover,.cart-container .shopping-cart-bottom .discount .content .coupon .actions-toolbar button.action:focus,.cart-container .shopping-cart-bottom .discount .content .coupon .actions-toolbar button.action:active, .checkout-index-index .modal-popup .modal-footer button:hover,.checkout-index-index .modal-popup .modal-footer button:active, .checkout-index-index .modal-popup .modal-footer button.primary, .checkout-container button:hover,.checkout-container button:active,.checkout-container button:focus , .checkout-container button.primary, .checkout-container .opc-wrapper .step-content .opc-payment-additional.discount-code .form-discount .actions-toolbar button:hover, .checkout-container .opc-wrapper .shipping-address-item:after, .opc-progress-bar .opc-progress-bar-item._active:before,.opc-progress-bar .opc-progress-bar-item._complete:before, .opc-progress-bar .opc-progress-bar-item._active > span:before,.opc-progress-bar .opc-progress-bar-item._complete > span:before, .single-deal-grid .product-item .box-deal-label, .deal-products-grid .product-item .product-photo .box-deal-label, .single-product .mgs-product .product-item-info .product-item-details .product-item-actions .btn-addto:hover,.single-product .mgs-product .product-item-info .product-item-details .product-item-actions .btn-addto:active,.single-product .mgs-product .product-item-info .product-item-details .product-item-actions .btn-addto:focus, .custom-layer-abs:after, .block-delivery-process .block-content .step-content [class*="step-"] .box-icon:hover .farm, .block-plant-about .col-1 .box-item .text .h2:before, .block-organie-app .block-content .box-item .item-title:hover h6 , .block-organie-app .block-content .box-item.active .item-title h6, .block-deal-flower .box-text a:hover, .custom-promo-button .promobanner .btn-promo-banner:hover, .block-flower-summer .btn-default:hover, .promobanner .box-white:hover .cms-about-us .about_us_1 .about_us_steps .step_item:hover .farm,.cms-about-us .about_us_1 .about_us_farm_services .farm_services_items .farm_services_item:hover .farm , .btn-tag:hover,.btn-tag:active,.btn-tag:focus, .blog-post-list .page-main .pages .pagination .item.current a , .blog-post-list .page-main .pages .pagination .item a:hover, .locator-index-index .pager .pages .pagination .item.current a, .locator-index-index .pager .pages .pagination .item a:hover, .portfolio-category-view .menu-portfolio ul.tab-menu li .btn:hover,.portfolio-category-view .menu-portfolio ul.tab-menu li .btn:focus,.portfolio-category-view .menu-portfolio ul.tab-menu li .btn:active , .portfolio-category-view .menu-portfolio ul.tab-menu li .btn.is-checked, .portfolio-category-view .tabs_categories_porfolio_content .item:hover .item_inner, .brand-index-index .characters ul.characters-filter li.active a, .brand-index-index .characters ul.characters-filter li a:hover, .brand-index-index .characters .view-all:hover ' => [
                'background-color' => $this->getStoreConfig('color/general/theme_color', $storeId)
            ],
            '.modal-popup .modal-footer .action-secondary:hover, .modal-popup .modal-footer .action-primary, .btn-default, .btn-default:hover,.btn-default:active,.btn-default:focus, .btn-primary, .btn-primary.disabled,.btn-primary[disabled],fieldset[disabled] .btn-primary,.btn-primary.disabled:hover,.btn-primary[disabled]:hover,fieldset[disabled] .btn-primary:hover,.btn-primary.disabled:focus,.btn-primary[disabled]:focus,fieldset[disabled] .btn-primary:focus,.btn-primary.disabled:active,.btn-primary[disabled]:active,fieldset[disabled] .btn-primary:active,.btn-primary.disabled.active,.btn-primary.active[disabled],fieldset[disabled] .btn-primary.active,.btn.disabled,.btn[disabled],fieldset[disabled] .btn, .btn-secondary:hover,.btn-secondary:focus,.btn-secondary:active, .form-control:focus, input:focus, .owl-carousel .owl-dots .owl-dot.active span,.owl-carousel .owl-dots .owl-dot:hover span, .navigation .nav-main li.dropdown .dropdown-menu, .block-cart-header .dropdown-menu, .block-cart-header .minicart-items-wrapper .minicart-items .product-item:hover .product-left .product-image-photo, .header .language .dropdown-menu,.header .currency .dropdown-menu, 
.header1 .top-header-links span.h4,.header5 .top-header-links span.h4,.header6 .top-header-links span.h4,.header1 .box-social-header span.h4,.header5 .box-social-header span.h4,.header6 .box-social-header span.h4, .header1 .box-social-header .block-social-header ul li a:hover,.header5 .box-social-header .block-social-header ul li a:hover,.header6 .box-social-header .block-social-header ul li a:hover, .header2 .block-search .dropdown-menu,.header3 .block-search .dropdown-menu,.header4 .block-search .dropdown-menu, .header2 .account-dropdown .dropdown-menu,.header4 .account-dropdown .dropdown-menu, .header4 .block-search .dropdown-menu, .header5 .top-bar-abs .language-current .account-dropdown .dropdown-menu, .block-social ul li a:hover, .scroll-to-top, .block-tab-products .nav-tabs li.active a,.block-tab-products .nav-tabs li a:hover, .btn-addto-default, .products-grid .product-item .product-item-info:hover, .products-grid .product-item .product-item-info:hover .product-item-name h4:after, .list-products .product-items .product-item .product-top .product-item-photo:before, .list-products .product-items .product-item .product-top .product-item-photo:after, .category-description blockquote , .sidebar .block-wishlist .block-content .product-items .product-item .product-item-info .product-item-photo .product-image-wrapper:before, .sidebar .block-wishlist .block-content .product-items .product-item .product-item-info .product-item-photo .product-image-wrapper:after , .products-list .product-item .product-item-details .product-item-inner .product-item-actions .btn-addto, .product-details-view .product-info-main .product-add-form .product-addto .btn-addto, .product-details-view .product.media .fotorama__thumb-border, .product.info.detailed .items > .item.title.active a, .bundle-options-container .block-bundle-summary .bundle-info .product-details .product-addto .btn-addto, .account.wishlist-index-index .products-grid .product-item .product-item-inner, #shopping-cart-table tbody tr td .actions-toolbar .gift-options-cart-item .action-gift, .checkout-index-index .modal-popup .modal-footer button , .checkout-container button , .checkout-container button.primary, .checkout-container .opc-wrapper .shipping-address-item.selected-item, .single-product .mgs-product .product-item-info .product-image .product-item-photo, .single-product .mgs-product .product-item-info .product-label span, .single-product .mgs-product .product-item-info .product-item-details .product-item-actions .btn-addto, .block-welcome-organie .box-cate-item:hover, .block-about-flower3 .box-img-main .img-1:before , .contact-index-index .form-control:focus, .btn-tag:hover,.btn-tag:active,.btn-tag:focus, .blog-post-view .blog-post .post-comments .post-comment .fieldset .field .input-text:focus, .portfolio-category-view .menu-portfolio ul.tab-menu li .btn:hover,.portfolio-category-view .menu-portfolio ul.tab-menu li .btn:focus,.portfolio-category-view .menu-portfolio ul.tab-menu li .btn:active, .portfolio-category-view .menu-portfolio ul.tab-menu li .btn.is-checked, .portfolio-index-view .portfolio-details .related-project .item:hover .item_inner, .catalog-product-compare-index .btn-primary:hover,.catalog-product-compare-index .btn-primary:focus, .catalog-product-compare-index .table-wrapper .product-image-photo:hover, .sidebar .block-brand .brand-list li.item a:hover .brand-image, .brand-index-index .characters ul.characters-filter li.active a , .brand-index-index .characters ul.characters-filter li a , .brand-index-index .characters ul.characters-filter li a:hover, .brand-index-index .characters .view-all, .brand-index-index .shop-by-brand .brand-list .item:hover .brand-content .brand-image ' => [
                'border-color' => $this->getStoreConfig('color/general/theme_color', $storeId)
            ],
            '.base-color, a:hover,a:focus, .btn-default, .owl-carousel .owl-nav [class*="owl-"]:hover, .navigation .nav-main li.active a,.navigation .nav-main li.active a:hover, .navigation .nav-main li a:hover, .navigation .nav-main li.dropdown .dropdown-menu li ul li a:hover, .block-cart-header .minicart-items-wrapper .minicart-items .product-item .product-item-details .product-item-name a:hover, .block-cart-header .minicart-items-wrapper .minicart-items .product-item .actions:hover, .nav-toggle:hover [class*="ion"], .block-search .minisearch .search-select #select-cat-dropdown span:hover, .block-search .minisearch .btn-primary:hover,.block-search .minisearch .search-autocomplete .amount, .block-search .btn-open-search:hover, .header .language .toggle:hover,.header .currency .toggle:hover,.header .language .toggle.active,.header .currency .toggle.active, .header .language .dropdown-menu ul li a:hover,.header .currency .dropdown-menu ul li a:hover, .header .language .dropdown-menu ul li.active a,.header .currency .dropdown-menu ul li.active a, .header .block-phone .phone-left p.h4, .top-bar-abs .nav-toggle-close:hover, .top-bar-abs .top-links li a:hover, .header2 .navigation .nav-main > li > a:hover, .header2 .account-dropdown .top-links-trigger:hover,.header4 .account-dropdown .top-links-trigger:hover, .header3 .top-bar .top-links li a:hover, .header4 .navigation .nav-main > li > a:hover, .header4 .block-search .btn-open-search:hover, .header4.header-sticky-menu .navigation .nav-main > li > a:hover, .header5 .top-bar-abs .language-current .account-dropdown .top-links-trigger:hover, .header5 .top-bar-abs .language-current .account-dropdown .dropdown-menu ul li a:hover, .header5 .top-bar-abs .navigation .nav-main > li a:hover, .header5 .top-bar-abs .navigation .nav-main > li .toggle-menu a:hover, .header5 .top-bar-abs .navigation .nav-main li.dropdown .dropdown-menu li ul li .toggle-menu a:hover, .color-default, .footer .block-contact a:hover, .footer .block-contact .color-default, .footer .block-info ul li a:hover,.footer .block-account ul li a:hover, .footer-links li a:hover, .footer2 .block-follow-instagram a.user-instagram:hover, .header.header6 .block-social-header-2 ul li a:hover, .header.header6 .navigation .nav-main > li > a:hover, .header.header6 .block-cart-header .showcart .box-shopbag .icon-cart [class*="ion"]:hover, .header.header6 .nav-toggle [class*="ion"]:hover, .header.header6.header-sticky-menu .navigation .nav-main > li > a:hover, body:not(.cms-home) .header.header6 .navigation .nav-main > li > a:hover, .scroll-to-top, .title-block p.h4, .btn-addto-default, .products-grid .product-item .product-item-info .product-top a.towishlist, .organie3-block-life .item-block [class*="icon-"], .mgs-blog-lastest-posts .read-more a:hover, .custom-style-blog-cake .mgs-blog-lastest-posts .item .post-tag a:hover, .custom-style-blog-cake .mgs-blog-lastest-posts .item .post-meta .date .day, .toolbar-amount .toolbar-number, .pages .pagination .item[class*="pages-item-"]:hover a,.pages .pagination .item[class*="pages-item-"]:active a,.pages .pagination .item[class*="pages-item-"]:hover span,.pages .pagination .item[class*="pages-item-"]:active span, .one-column-filter .filter-current .items .item .action.remove, .one-column-filter .filter-clear a, .sidebar .filter .filter-options .filter-option .filter-body .items li.item a:hover, .sidebar .block-compare .product-items .product-item a.action, .sidebar .block-compare .actions-toolbar .secondary a, .sidebar .account-nav .item strong:hover,.sidebar .account-nav .item a:hover, .sidebar .account-nav .item strong, .sidebar .account-nav .item.current strong, .sidebar .block-brand .view-all, .products-list .product-item .product-item-details .product-item-inner .product-item-actions .btn-addto, .product-details-view .product-info-main .product-top-info .product-reviews-summary .reviews-actions a:hover, .product-details-view .product-info-main .product-info-stock-sku ul li .value a, .product-details-view .product-info-main .product-add-form .product-addto .btn-addto, .product-details-view .product-info-main .product-brand .brand-name a:hover, .box-tocart .field.qty .control .edit-qty:hover, .box-tocart .field.qty .available.stock [class*="ion-"], .review-add .block-title .count,.review-list .block-title .count, .bundle-options-container .block-bundle-summary .bundle-info .product-details .product-addto .btn-addto, .account .block-title a.action, .account.wishlist-index-index .products-grid .product-item .product-item-inner .product-item-actions a, .cart-container .cart.actions button.clear.btn [class*="ion-ios-"], .cart-container .cart.actions button.clear.btn:hover,.cart-container .cart.actions button.clear.btn:focus,.cart-container .cart.actions button.clear.btn.active, .cart-container .shopping-cart-bottom .cart-totals .content table.totals tbody tr.sub td .price, .cart-container .shopping-cart-bottom .cart-totals .content table.totals tbody tr.grand td .price, .cart-container .shopping-cart-bottom .checkout-methods-items .multicheckout, .checkout-index-index .authentication-wrapper .action-auth-toggle, .checkout-index-index .authentication-wrapper .authentication-dropdown .action-close:hover, .checkout-container button, .checkout-container .opc-wrapper .shipping-address-item .action-select-shipping-item, .checkout-container .opc-sidebar .table-totals tbody tr.grand td .price, .checkout-container .opc-sidebar .opc-block-shipping-information .shipping-information .shipping-information-title .action:before, .checkout-container .payment-method-billing-address .fieldset .actions-toolbar .action-cancel:hover,.checkout-container .payment-method-billing-address .fieldset .actions-toolbar .action-cancel:active,.checkout-container .payment-method-billing-address .fieldset .actions-toolbar .action-cancel:focus, [class*="multishipping-checkout-"] .page-main .multicheckout.shipping .block-shipping .block-content .box .box-title .action, .deal-timer .countdown span, .single-deal-grid .product-item .product-item-name a , .single-product .mgs-product .product-item-info .product-item-details .product-item-name h4, .single-product .mgs-product .product-item-info .product-item-details .categories-link .value a, .single-product .mgs-product .product-item-info .product-item-details .product-item-actions .btn-addto, .portfolio-list-block .item .portfolio-top-content .hover-info span.fa, .portfolio-list-block .item .portfolio-top-content .hover-info span .categories a, .flower-block-about .box-item a.read-more:hover , .testimonial-block .owl-carousel .owl-nav [class*="owl-"]:hover, .social-tweet .tweet-container .tweet-content a, .social-tweet .tweet-container .tweet-content .times a:hover, .block-delivery-process .block-content .step-content [class*="step-"] .box-icon .farm, .block-farm-services .block-content .farm_services_item .farm, .block-farm-services .block-content .farm_services_item a:hover, .custom-deal-layout2 .single-deal-grid .product-item .product-item-details .box-attribute dl dd a:hover, .block-plant-about .col-1 .box-item:hover .text .h2 , .block-counter .counter-number .counter, .block-organie-video .title, .block-organie-app .title .h4, .block-organie-app .block-content .box-item .item-title h6 span, .block-plant-about-2 .block-content .title-about h3 , .block-plant-about-2 .block-content .section-2 .item .plant, .block-plant-about-2 .block-content .section-2 .item a:hover, .deal-desc-hidden .deal-product-cat-block .single-deal-grid .product-item .deal-timer .countdown span .timer, .block-fresh-spring h6.last a:hover, .block-img-deal p.h2 , .custom-deal-layout-3 .single-deal-grid .product-item .box-attribute dl dd .price-box .special-price .price, .custom-deal-layout-3 .single-deal-grid .product-item .deal-timer .countdown span .timer, .block-flower-summer .btn-default , .breadcrumbs .breadcrumb li:last-child, .cms-about-us p.h4, .cms-about-us .about_us_1 .about_us_steps .step_item .farm, .cms-about-us .about_us_1 .about_us_farm_services .farm_services_items .farm_services_item .farm , .cms-about-us .about_us_1 .about_us_farm_services .farm_services_items .farm_services_item a:hover , .cms-about-us-2 p.h4, .cms-about-us-2 .about_us_2 .about_us_story .center .center_inner .title_section p, .cms-about-us-2 .about_us_2 .about_us_story .center .center_inner .content_story p span , .blog-post-list .page-main .pages .pagination .item.pages-item-next a:hover,.blog-post-list .page-main .pages .pagination .item.pages-item-previous a:hover , .blog-post-list .page-main .sharethis-inline-share-buttons span.sharethis:hover, .blog-post-list .page-main .post-title a:hover, .blog-post-list .page-main .post-info .published-by a:hover, .blog-post-list .page-main .blog-masonry .grid-item .post-link a:hover , .blog-post-list .page-main .sidebar .block-blog-categories .block-content .category-list .item .category-info a:hover, .blog-post-list .page-main .sidebar .block-blog-posts .block-content .post-list .item .post-name a:hover, .blog-post-view .blog-post .sharethis-inline-share-buttons span.sharethis:hover, .blog-post-view .blog-post .post-actions .action a .fa, .blog-post-view .blog-post .post-info .published-by a:hover , .blog-post-view .blog-post .post-comments .comment-count .count, .blog-post-view .sidebar .block-blog-categories .block-content .category-list .item .category-info a:hover , .blog-post-view .sidebar .block-blog-posts .block-content .post-list .item .post-name a:hover, .locator-index-index .pager .pages .pagination .item.pages-item-next a:hover,.locator-index-index .pager .pages .pagination .item.pages-item-previous a:hover, .portfolio-category-view .tabs_categories_porfolio_content .item .portfolio-bottom-content .category-link a:hover, .portfolio-index-view .portfolio-details .related-project .item .portfolio-bottom-content .category-link a:hover , .catalog-product-compare-index .btn-primary:hover,.catalog-product-compare-index .btn-primary:focus, .sidebar .block-brand .brand-list li.item a:hover span, .brand-index-index .characters ul.characters-filter li a, .brand-index-index .characters .view-all a, .brand-index-index .shop-by-brand .brand-list .brand-title .count, .brand-index-index .shop-by-brand .brand-list .item:hover .brand-content a, .block-custom-product a:after ' => 
            [
                'color' => $this->getStoreConfig('color/general/theme_color', $storeId)
            ]
        ];
        $setting = array_filter($setting);
        return $setting;
    }
	
	// get header custom color
    public function getHeaderColorSetting($storeId) {
        $setting = [
            /* Header Top Section */
            '.top-bar' => [
                'background-color' => $this->getStoreConfig('color/header/background_color', $storeId),
                'color' => $this->getStoreConfig('color/header/text_color', $storeId)
            ],
			'.top-header-content .dropdown .action' => [
                'color' => $this->getStoreConfig('color/header/text_color', $storeId)
            ],
			'.top-header-content a' => [
                'color' => $this->getStoreConfig('color/header/link_color', $storeId)
            ],
			'.top-header-content a:hover' => [
                'color' => $this->getStoreConfig('color/header/link_hover_color', $storeId)
            ],
			'.top-header-content .dropdown .ui-dialog' => [
                'background-color' => $this->getStoreConfig('color/header/dropdown_background', $storeId)
            ],
			'.top-header-content .switcher .switcher-options .ui-widget-content ul.switcher-dropdown li a' => [
                'color' => $this->getStoreConfig('color/header/dropdown_link_color', $storeId)
            ],
			'.top-header-content .switcher .switcher-options .ui-widget-content ul.switcher-dropdown li a:hover' => [
                'color' => $this->getStoreConfig('color/header/dropdown_link_hover_color', $storeId)
            ],
			/* Header Middle Section */
			'.middle-header-container' => [
                'background-color' => $this->getStoreConfig('color/header/middle_background', $storeId)
            ],
			/* Top Search Section */
			'#search_mini_form .input-text' => [
                'background-color' => $this->getStoreConfig('color/header/search_input_background', $storeId),
                'border-color' => $this->getStoreConfig('color/header/search_input_border', $storeId),
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId),
            ],
			'#search_mini_form .input-text::-webkit-input-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'#search_mini_form .input-text:-moz-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'#search_mini_form .input-text::-moz-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'#search_mini_form .input-text:-ms-input-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'#search_mini_form .btn-primary' => [
                'background-color' => $this->getStoreConfig('color/header/search_button_background', $storeId),
                'border-color' => $this->getStoreConfig('color/header/search_button_background', $storeId),
                'color' => $this->getStoreConfig('color/header/search_button_text', $storeId)
            ],
			'#search_mini_form .btn-primary:hover' => [
                'background-color' => $this->getStoreConfig('color/header/search_button_background_hover', $storeId),
                'border-color' => $this->getStoreConfig('color/header/search_button_background_hover', $storeId),
                'color' => $this->getStoreConfig('color/header/search_button_text_hover', $storeId)
            ],
			/* Top Cart Section */
			'.block-cart-header .showcart .box-shopbag .icon-cart [class*="ion"]' => [
                'color' => $this->getStoreConfig('color/header/cart_icon', $storeId)
            ],
			'.block-cart-header .showcart .box-shopbag .icon-cart .count' => [
                'background-color' => $this->getStoreConfig('color/header/cart_number_background', $storeId),
                'color' => $this->getStoreConfig('color/header/cart_number', $storeId)
            ],
			'.block-cart-header .dropdown-menu' => [
                'background-color' => $this->getStoreConfig('color/header/cart_dropdown_background', $storeId),
                'border-color' => $this->getStoreConfig('color/header/cart_dropdown_border', $storeId),
            ],
			'.block-cart-header .dropdown-menu .block-content .subtitle.empty, .block-cart-header .dropdown-menu .block-content,.block-cart-header .subtotal .label,.block-cart-header .minicart-items-wrapper .minicart-items .product-item .product-item-details .product-item-pricing .price-container .price' => [
                'color' => $this->getStoreConfig('color/header/cart_dropdown_text', $storeId)
            ],
			'.block-cart-header .minicart-items-wrapper .minicart-items .product-item .action, .block-cart-header .minicart-items-wrapper a, .block-cart-header .minicart-items-wrapper .minicart-items .product-item .product-item-details .product-item-name a' => [
                'color' => $this->getStoreConfig('color/header/cart_dropdown_link', $storeId)
            ],
			'.block-cart-header .minicart-items-wrapper .minicart-items .product-item .action:hover, .block-cart-header .minicart-items-wrapper a:hover, .block-cart-header .minicart-items-wrapper .minicart-items .product-item .product-item-details .product-item-name a:hover' => [
                'color' => $this->getStoreConfig('color/header/cart_dropdown_link_hover', $storeId)
            ],
			'.minicart-wrapper .ui-widget-content button, .minicart-wrapper .ui-widget-content .btn' => [
                'background-color' => $this->getStoreConfig('color/header/cart_dropdown_button_background', $storeId),
                'border-color' => $this->getStoreConfig('color/header/cart_dropdown_button_background', $storeId),
                'color' => $this->getStoreConfig('color/header/cart_dropdown_button_text', $storeId),
            ],
			'.minicart-wrapper .ui-widget-content button:hover, .minicart-wrapper .ui-widget-content .btn:hover' => [
                'background-color' => $this->getStoreConfig('color/header/cart_dropdown_button_background_hover', $storeId),
                'border-color' => $this->getStoreConfig('color/header/cart_dropdown_button_background_hover', $storeId),
                'color' => $this->getStoreConfig('color/header/cart_dropdown_button_text_hover', $storeId),
            ],
			/* Top Search Section */
			'#search_mini_form .input-text' => [
                'background-color' => $this->getStoreConfig('color/header/search_input_background', $storeId),
                'border-color' => $this->getStoreConfig('color/header/search_input_border', $storeId),
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId),
            ],
			'#search_mini_form .input-text::-webkit-input-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'#search_mini_form .input-text:-moz-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'#search_mini_form .input-text::-moz-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'#search_mini_form .input-text:-ms-input-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'#search_mini_form .btn-primary' => [
                'background-color' => $this->getStoreConfig('color/header/search_button_background', $storeId),
                'border-color' => $this->getStoreConfig('color/header/search_button_background', $storeId),
                'color' => $this->getStoreConfig('color/header/search_button_text', $storeId)
            ],
			'#search_mini_form .btn-primary:hover' => [
                'background-color' => $this->getStoreConfig('color/header/search_button_background_hover', $storeId),
                'border-color' => $this->getStoreConfig('color/header/search_button_background_hover', $storeId),
                'color' => $this->getStoreConfig('color/header/search_button_text_hover', $storeId)
            ],
			/* Menu Section */
			'nav.navigation' => [
                'background-color' => $this->getStoreConfig('color/header/menu_background', $storeId)
            ],
			'nav.navigation #mainMenu > .level0' => [
                'background-color' => $this->getStoreConfig('color/header/lv1_background', $storeId)
            ],
			'#mainMenu .level0 a.level0' => [
                'color' => $this->getStoreConfig('color/header/lv1_color', $storeId)
            ],
			'nav.navigation #mainMenu > .level0:hover' => [
                'background-color' => $this->getStoreConfig('color/header/lv1_background_hover', $storeId)
            ],
			'nav.navigation #mainMenu .level0:hover > a, nav.navigation #mainMenu .level0 > a.ui-state-focus, nav.navigation #mainMenu .level0.active > a' => [
                'color' => $this->getStoreConfig('color/header/lv1_color_hover', $storeId)
            ],
			'nav.navigation li.level0 ul.dropdown-menu, nav.navigation #mainMenu .level0 ul.level0, nav.navigation #mainMenu .level0 ul.level0 li.level1 ul.level1' => [
                'background-color' => $this->getStoreConfig('color/header/menu_dropdown_background', $storeId)
            ],
			'nav.navigation li.level0 ul.dropdown-menu li a, .navigation .nav-main li.dropdown .dropdown-menu li ul li a' => [
                'color' => $this->getStoreConfig('color/header/menu_dropdown_link_color', $storeId)
            ],
			'nav.navigation li.level0 ul.dropdown-menu:hover' => [
                'background-color' => $this->getStoreConfig('color/header/menu_dropdown_background_hover', $storeId)
            ],
			'nav.navigation li.level0 ul.dropdown-menu > li:hover a, .navigation .nav-main li.dropdown .dropdown-menu li ul li a:hover, .navigation .nav-main li.dropdown .dropdown-menu li ul li a:focus, .navigation .nav-main li.dropdown .dropdown-menu li ul li a:active' => [
                'color' => $this->getStoreConfig('color/header/menu_dropdown_link_color_hover', $storeId)
            ],
            '.nav-toggle [class*="ion"]'  => [
              'color' => $this->getStoreConfig('color/header/menu_navbar_color',$storeId)  
            ],
            '.nav-toggle [class*="ion"]:hover' => [
                'color' =>  $this->getStoreConfig('color/header/menu_navbar_hover_color',$storeId) 
            ],
        ];
        $setting = array_filter($setting);
        return $setting;
    }
	
	// get main content custom color
    public function getMainColorSetting($storeId) {
        $setting = [
            /* Text & Link color */
            '.page-main' => [
                'color' => $this->getStoreConfig('color/main/text_color', $storeId)
            ],
			'.page-main a' => [
                'color' => $this->getStoreConfig('color/main/link_color', $storeId)
            ],
			'.page-main a:hover, .page-main a:focus' => [
                'color' => $this->getStoreConfig('color/main/link_color_hover', $storeId)
            ],
			'.page-main .price, .page-main .price-box .price, .price-box .price, .price' => [
                'color' => $this->getStoreConfig('color/main/price_color', $storeId)
            ],
			/* Default button color */
            'button, button.btn, button.btn-default' => [
                'color' => $this->getStoreConfig('color/main/button_text', $storeId),
                'background-color' => $this->getStoreConfig('color/main/button_background', $storeId),
                'border-color' => $this->getStoreConfig('color/main/button_border', $storeId)
            ],
			'button:hover, button.btn:hover, button.btn-default:hover, btn:hover, .btn-default:focus, .btn-default:active, button.btn:active, button.btn:focus, button:focus' => [
                'color' => $this->getStoreConfig('color/main/button_text_hover', $storeId),
                'background-color' => $this->getStoreConfig('color/main/button_background_hover', $storeId),
                'border-color' => $this->getStoreConfig('color/main/button_border_hover', $storeId)
            ],
			/* Primary button color */
            'button.btn-primary' => [
                'color' => $this->getStoreConfig('color/main/primary_button_text'),
                'background-color' => $this->getStoreConfig('color/main/primary_button_background', $storeId),
                'border-color' => $this->getStoreConfig('color/main/primary_button_border', $storeId)
            ],
			'button.btn-primary:hover, button.btn-primary:focus' => [
                'color' => $this->getStoreConfig('color/main/primary_button_text_hover', $storeId),
                'background-color' => $this->getStoreConfig('color/main/primary_button_background_hover', $storeId),
                'border-color' => $this->getStoreConfig('color/main/primary_button_border_hover', $storeId)
            ],
			/* Secondary button color */
            'button.btn-secondary' => [
                'color' => $this->getStoreConfig('color/main/secondary_button_text', $storeId),
                'background-color' => $this->getStoreConfig('color/main/secondary_button_background', $storeId),
                'border-color' => $this->getStoreConfig('color/main/secondary_button_border', $storeId)
            ],
			'button.btn-secondary:hover, button.btn-secondary:focus' => [
                'color' => $this->getStoreConfig('color/main/secondary_button_text_hover', $storeId),
                'background-color' => $this->getStoreConfig('color/main/secondary_button_background_hover', $storeId),
                'border-color' => $this->getStoreConfig('color/main/secondary_button_border_hover', $storeId)
            ],
        ];
        $setting = array_filter($setting);
        return $setting;
    }
	
	// get main content custom color
    public function getFooterColorSetting($storeId) {
        $setting = [
            /* Top Footer Section */
            'footer .top-footer' => [
                'background-color' => $this->getStoreConfig('color/footer/top_background_color', $storeId),
                'color' => $this->getStoreConfig('color/footer/top_text_color', $storeId),
                'border-color' => $this->getStoreConfig('color/footer/top_border_color', $storeId)
            ],
			'footer .top-footer label' => [
                'color' => $this->getStoreConfig('color/footer/top_text_color', $storeId)
            ],
			'footer .top-footer h1,footer .top-footer h2,footer .top-footer h3,footer .top-footer h4,footer .top-footer h5,footer .top-footer h6' => [
                'color' => $this->getStoreConfig('color/footer/top_heading_color', $storeId),
            ],
			'footer .top-footer a' => [
                'color' => $this->getStoreConfig('color/footer/top_link_color', $storeId),
            ],
			'footer .top-footer a:hover' => [
                'color' => $this->getStoreConfig('color/footer/top_link_color_hover', $storeId),
            ],
			'footer .top-footer .fa' => [
                'color' => $this->getStoreConfig('color/footer/top_icon_color', $storeId),
            ],
			/* Middle Footer Section */
            'footer .middle-footer' => [
                'background-color' => $this->getStoreConfig('color/footer/middle_background_color', $storeId),
                'color' => $this->getStoreConfig('color/footer/middle_text_color', $storeId),
                'border-color' => $this->getStoreConfig('color/footer/middle_border_color', $storeId)
            ],
			'footer .middle-footer label, .footer .block-contact ul li, footer .middle-footer' => [
                'color' => $this->getStoreConfig('color/footer/middle_text_color', $storeId)
            ],
			'footer .middle-footer h1,footer .middle-footer h2,footer .middle-footer h3,footer .middle-footer h4,footer .middle-footer h5,footer .middle-footer h6' => [
                'color' => $this->getStoreConfig('color/footer/middle_heading_color', $storeId),
            ],
			'footer .middle-footer a, .footer .block-account ul li a, .footer .block-info ul li a, .footer .block-social ul li a, .footer .block-info ul li a:before, .footer .block-account ul li a:before, .footer .block-contact a' => [
                'color' => $this->getStoreConfig('color/footer/middle_link_color', $storeId),
            ],
			'footer .middle-footer a:hover,  .footer .block-account ul li a;hover, .footer .block-info ul li a:hover,  .footer .block-social ul li a:hover, .footer .block-contact a:hover' => [
                'color' => $this->getStoreConfig('color/footer/middle_link_color_hover', $storeId),
            ],
			'footer .middle-footer .fa, footer .middle-footer ["class*=ion-"]' => [
                'color' => $this->getStoreConfig('color/footer/middle_icon_color', $storeId),
            ],
			/* Bottom Footer Section */
            'footer .bottom-footer' => [
                'background-color' => $this->getStoreConfig('color/footer/bottom_background_color', $storeId),
                'color' => $this->getStoreConfig('color/footer/bottom_text_color', $storeId)
                
            ],
              '.footer1 .bottom-footer .bottom-ft-container'=> [
                'border-color' => $this->getStoreConfig('color/footer/bottom_border_color', $storeId)
            ],
			'footer .bottom-footer label' => [
                'color' => $this->getStoreConfig('color/footer/bottom_text_color', $storeId)
            ],
			'footer .bottom-footer h1,footer .bottom-footer h2,footer .bottom-footer h3,footer .bottom-footer h4,footer .bottom-footer h5,footer .bottom-footer h6' => [
                'color' => $this->getStoreConfig('color/footer/bottom_heading_color', $storeId),
            ],
			'footer .bottom-footer a, .footer-links li a' => [
                'color' => $this->getStoreConfig('color/footer/bottom_link_color', $storeId),
            ],
			'footer .bottom-footer a:hover, .footer-links li a:hover' => [
                'color' => $this->getStoreConfig('color/footer/bottom_link_color_hover', $storeId),
            ],
			'footer .bottom-footer .fa' => [
                'color' => $this->getStoreConfig('color/footer/bottom_icon_color', $storeId),
            ],
        ];
        $setting = array_filter($setting);
        return $setting;
    }
	
	/* Get css content of panel */
	public function getPanelStyle(){
		$dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Mpanel/view/frontend/web/css/panel.css');
		$content = file_get_contents($dir);
		return $content;
	}
	
	/* Check store view has use homepage builder or not */
	public function useBuilder(){
		if($this->_useBuilder){
			return true;
		}else{
			$storePanelCollection = $this->getModel('MGS\Mpanel\Model\Store')
				->getCollection()
				->addFieldToFilter('store_id', $this->getStore()->getId())
				->addFieldToFilter('status', 1);
			if(count($storePanelCollection)>0){
				$this->_useBuilder = true;
				return true;
			}
			$this->_useBuilder = false;
			return false;
		}
		
	}
	
	/* Check current page is homepage or not */
	public function isHomepage(){
		if ($this->_fullActionName == 'cms_index_index') {
			return true;
		}
		return false;
	}
	
	/* Get Animation Effect */
	public function getAnimationEffect(){
		return [
			'bounce' => 'Bounce',
			'flash' => 'Flash',
			'pulse' => 'Pulse',
			'rubberBand' => 'Rubber Band',
			'shake' => 'Shake',
			'swing' => 'Swing',
			'tada' => 'Tada',
			'wobble' => 'Wobble',
			'bounceIn' => 'Bounce In',
			'fadeIn' => 'Fade In',
			'fadeInDown' => 'Fade In Down',
			'fadeInDownBig' => 'Fade In Down Big',
			'fadeInLeft' => 'Fade In Left',
			'fadeInLeftBig' => 'Fade In Left Big',
			'fadeInRight' => 'Fade In Right',
			'fadeInRightBig' => 'Fade In Right Big',
			'fadeInUp' => 'Fade In Up',
			'fadeInUpBig' => 'Fade In Up Big',
			'flip' => 'Flip',
			'flipInX' => 'Flip In X',
			'flipInY' => 'Flip In Y',
			'lightSpeedIn' => 'Light Speed In',
			'rotateIn' => 'Rotate In',
			'rotateInDownLeft' => 'Rotate In Down Left',
			'rotateInDownRight' => 'Rotate In Down Right',
			'rotateInUpLeft' => 'Rotate In Up Left',
			'rotateInUpRight' => 'Rotate In Up Right',
			'rollIn' => 'Roll In',
			'zoomIn' => 'Zoom In',
			'zoomInDown' => 'Zoom In Down',
			'zoomInLeft' => 'Zoom In Left',
			'zoomInRight' => 'Zoom In Right',
			'zoomInUp' => 'Zoom In Up',
		];
	}
	
	public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->_request->isSecure()], $params);
            return $this->_assetRepo->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
            return $this->_getNotFoundUrl();
        }
    }
	
	public function getColorAccept($type, $color = NULL) {
		$dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Mpanel/view/frontend/web/images/panel/colour/');
        $html = '';
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                $html .= '<ul>';

                while ($files[] = readdir($dh));
                sort($files);
                foreach ($files as $file) {
                    $file_parts = pathinfo($dir . $file);
                    if (isset($file_parts['extension']) && $file_parts['extension'] == 'png') {
                        $colour = str_replace('.png', '', $file);
                        $wrapper = str_replace('_', '-', $type);
						$_color = explode('.', $colour);
                        $colour = $wrapper . '-' . strtolower(end($_color));
                        $html .= '<li>';
                        $html .= '<a href="#" onclick="changeInputColor(\'' . $colour . '\', \'' . $type . '\', this, \'' . $wrapper . '-content\'); return false"';
                        if ($color != NULL && $color == $colour) {
                            $html .= ' class="active"';
                        }
                        $html .= '>';
                         $html .= '<img src="' . $this->getViewFileUrl('MGS_Mpanel::images/panel/colour/'.$file) . '" alt=""/>';
                        $html .= '</a>';
                        $html .= '</li>';
                    }
                }
                $html .= '</ul>';
            }
        }
        return $html;
    }
	
	public function convertPerRowtoCol($perRow){
		switch ($perRow) {
            case 1:
                $result = 12;
                break;
            case 2:
                $result = 6;
                break;
            case 3:
                $result = 4;
                break;
            case 4:
                $result = 3;
                break;
            case 5:
                $result = 'custom-5';
                break;
            case 6:
                $result = 2;
				break;
			case 7:
                $result = 'custom-7';
				break;
			case 8:
                $result = 'custom-8';
                break;
        }
		
		return $result;
	}
	
	public function convertColClass($col, $type){
		if(($type=='row')){
                    if($col=='custom-5' || $col=='custom-7' || $col=='custom-8'){
                        return 'row-'.$col;
                    }else{
                        if($col == 2){
                                return 'row-custom-'.$col;
                            }
                    }   
		}
		if($type=='col'){
			if(($col=='custom-5' || $col=='custom-7' || $col=='custom-8')){
				return 'col-md-'.$col;
			}else{
				$class = 'col-lg-'.$col.' col-md-'.$col;
				if(($col==12) || ($col==6)){
					$class .= ' col-sm-6 col-xs-6';
				}
				if(($col==4) || ($col==3)){
					$class .= ' col-sm-4 col-xs-6';
				}
				if($col==2){
					$class .= ' col-sm-4 col-xs-6';
				}
				
				return $class;
			}
		}
	}
	
	public function getRootCategory(){
		$store = $this->getStore();
		$categoryId = $store->getRootCategoryId();
		$category = $this->getModel('Magento\Catalog\Model\Category')->load($categoryId);
		return $category;
	}
	
	public function getTreeCategory($category, $parent, $ids = array(), $checkedCat){
		$rootCategoryId = $this->getRootCategory()->getId();
		$children = $category->getChildrenCategories();
		$childrenCount = count($children);
		//$checkedCat = explode(',',$checkedIds);
		$htmlLi = '<li lang="'.$category->getId().'">';
		$html[] = $htmlLi;
		//if($this->isCategoryActive($category)){
		$ids[] = $category->getId();
		//$this->_ids = implode(",", $ids);
		//}
		
		$html[] = '<a id="node'.$category->getId().'">';

		if($category->getId() != $rootCategoryId){
			$html[] = '<input lang="'.$category->getId().'" type="checkbox" id="radio'.$category->getId().'" name="setting[category_id][]" value="'.$category->getId().'" class="checkbox'.$parent.'"';
			if(in_array($category->getId(), $checkedCat)){
				$html[] = ' checked="checked"';
			}
			$html[] = '/>';
		}
		

		$html[] = '<label for="radio'.$category->getId().'">' . $category->getName() . '</label>';

		$html[] = '</a>';
		
		$htmlChildren = '';
		if($childrenCount>0){
			foreach ($children as $child) {
				$_child = $this->getModel('Magento\Catalog\Model\Category')->load($child->getId());
				$htmlChildren .= $this->getTreeCategory($_child, $category->getId(), $ids, $checkedCat);
			}
		}
		if (!empty($htmlChildren)) {
            $html[] = '<ul id="container'.$category->getId().'">';
            $html[] = $htmlChildren;
            $html[] = '</ul>';
        }

        $html[] = '</li>';
        $html = implode("\n", $html);
        return $html;
	}
	
	public function truncate($content, $length){
		return $this->filterManager->truncate($content, ['length' => $length, 'etc' => '']);
	}
	
	public function convertToLayoutUpdateXml($child){
		$settings = json_decode($child->getSetting(), true);
		$content = $child->getBlockContent();
		$content = preg_replace('/(mgs_panel_title="")/i', '', $content);
		$content = preg_replace('/(mgs_panel_title=".+?)+(")/i', '', $content);
		$content = preg_replace('/(mgs_panel_note="")/i', '', $content);
		$content = preg_replace('/(mgs_panel_note=".+?)+(")/i', '', $content);
		$content = preg_replace('/(labels=".+?)+(")/i', '', $content);
		$arrContent = explode(' ',$content);
		$arrContent = array_filter($arrContent);
		$class = $arrContent[1];
		$class = str_replace('type=','class=',$class);
		unset($arrContent[0], $arrContent[1]);
		
		$lastData = end($arrContent);
		//print_r($arrContent); die();
		array_pop($arrContent);
		
		$arrContent = array_values($arrContent);

		$argumentString = '&nbsp;&nbsp;&nbsp;&nbsp;&lt;arguments&gt;<br/>';
		
		if(isset($settings['title']) && ($settings['title']!='')){
			$argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="mgs_panel_title" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['title'])).'&lt;/argument&gt;<br/>';
		}
		if(isset($settings['additional_content']) && ($settings['additional_content']!='')){
			$argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="mgs_panel_note" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['additional_content'])).'&lt;/argument&gt;<br/>';
		}
		
		if((isset($settings['tabs']) && ($settings['tabs']!='')) && (!isset($settings['hide_cover']))){
			usort($settings['tabs'], function ($item1, $item2) {
				if ($item1['position'] == $item2['position']) return 0;
				return $item1['position'] < $item2['position'] ? -1 : 1;
			});
			$tabType = $tabLabel = [];
			foreach($settings['tabs'] as $tab){
				$tabLabel[] = $tab['label'];
			}
			$labels = implode(',',$tabLabel);
			$argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="labels" xsi:type="string"&gt;'.$labels.'&lt;/argument&gt;<br/>';
		}
		$template = '';
		foreach($arrContent as $argument){
			$argumentData = explode('=',$argument);
			if($argumentData[0]!='template' && isset($argumentData[0]) && isset($argumentData[1])){
				$argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="'.$argumentData[0].'" xsi:type="string"&gt;'.str_replace('"','',$argumentData[1]).'&lt;/argument&gt;<br/>';
			}else{
				$template = $argumentData[1];
			}
			
		}
		
		
		$html = '&lt;block '.$class;
		
		$lastDataArr = explode('=',$lastData);
		if($lastDataArr[0]=='template'){
			$template = str_replace('}}','',$lastDataArr[1]);
		}else{
			$argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="'.$lastDataArr[0].'" xsi:type="string"&gt;'.str_replace('"','',str_replace('}}','',$lastDataArr[1])).'&lt;/argument&gt;<br/>';
		}
		
		$html .= ' template='.$template;
		
		$argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&lt;/arguments&gt;';
		
		$html .= '&gt;<br/>';
		$html .= $argumentString;
		$html .= '<br/>&lt;/block&gt;';
		
		return $html;
	}
	
	/* Get all images from pub/media/wysiwyg/$type folder */
	public function getPanelUploadImages($type){
		$path = 'wysiwyg/'.$type.'/';
		$dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($path);
		$result = [];
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while ($files[] = readdir($dh));
                sort($files);
                foreach ($files as $file) {
                    $file_parts = pathinfo($dir . $file);
                    if (isset($file_parts['extension']) && in_array(strtolower($file_parts['extension']), ['jpg', 'jpeg', 'png', 'gif'])) {
                        $result[] = $file;
                    }
                }
            }
        }
        return $result;
	}
	
	/* Convert short code to insert image */
	public function convertImageWidgetCode($type, $image){
		return '&lt;img src="{{media url="wysiwyg/'.$type.'/'.$image.'"}}" alt=""/&gt;';
	}
	
	public function encodeHtml($html){
		$result = str_replace("<","&lt;",$html);
		$result = str_replace(">","&gt;",$result);
		$result = str_replace('"','&#34;',$result);
		$result = str_replace("'","&#39;",$result);
		return $result;
	}
	
	public function decodeHtmlTag($content){
		$result = str_replace("&lt;","<",$content);
		$result = str_replace("&gt;",">",$result);
		$result = str_replace('&#34;','"',$result);
		$result = str_replace("&#39;","'",$result);
		return $result;
	}
	
	public function getCmsBlockByIdentifier($identifier){
		$block = $this->_blockFactory->create();
		$block->setStoreId($this->getStore()->getId())->load($identifier);
		return $block;
	}
	
	public function getPageById($id){
		$page = $this->_pageFactory->create();
		$page->setStoreId($this->getStore()->getId())->load($id, 'identifier');
		return $page;
	}
	
	public function getHeaderClass(){
		$header = $this->getStoreConfig('mgstheme/general/header');
		$class = str_replace('.phtml', '', $header);
		$class = str_replace('_', '', $class);
		if($this->_acceptToUsePanel){
			$class .= ' builder-container header-builder';
		}
		return $class;
	}
	
	public function getFooterClass(){
		$footer = $this->getStoreConfig('mgstheme/general/footer');
		$class = str_replace('.phtml', '', $footer);
		$class = str_replace('_', '', $class);
		if($this->_acceptToUsePanel){
			$class .= ' builder-container footer-builder';
		}
		return $class;
	}
	
	public function getContentVersion($type, $themeId){
		$theme = $this->getModel('Magento\Theme\Model\Theme')->load($themeId);
		$themePath = $theme->getThemePath();
		$dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('design/frontend/'.$themePath.'/Magento_Theme/templates/html/'.$type);
		
		$result = [];
		$files = [];
		if(is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while ($files[] = readdir($dh));
				sort($files);
				foreach ($files as $file){
					$file_parts = pathinfo($dir . $file);
					if (isset($file_parts['extension']) && $file_parts['extension'] == 'phtml') {
                        $fileName = str_replace('.phtml', '', $file);
                        $result[] = array('value' => $fileName, 'label' => $this->convertFilename($fileName), 'path'=>$themePath);
                    }
				}
                closedir($dh);
            }
        }
		
		if(count($result)==0){
			$dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('design/frontend/Mgs/mgsblank/Magento_Theme/templates/html/'.$type);
			if(is_dir($dir)) {
				if ($dh = opendir($dir)) {
					while ($files[] = readdir($dh));
					sort($files);
					foreach ($files as $file){
						$file_parts = pathinfo($dir . $file);
						if (isset($file_parts['extension']) && $file_parts['extension'] == 'phtml') {
							$fileName = str_replace('.phtml', '', $file);
							$result[] = array('value' => $fileName, 'label' => $this->convertFilename($fileName), 'path'=>'mgsblank');
						}
					}
					closedir($dh);
				}
			}
		}
		return $result;
	}
	
	public function convertFilename($filename){
		$filename = str_replace('_',' ',$filename);
		$filename = ucfirst($filename);
		return $filename;
	}
	
	public function isFile($path, $type, $fileName){
		$path = str_replace('Mgs/','',$path);
		$filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/'.$path.'/'.$type.'s/') . $fileName.'.png';
		if ($this->_file->isExists($filePath))  {
			return $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).'mgs/'.$path.'/'.$type.'s/' . $fileName.'.png';
		}
		return false;
	}
	
	public function getCurrentCategory(){
		if($this->_currentCategory){
			return $this->_currentCategory;
		}else{
			$id = $this->_request->getParam('id');
			$this->_currentCategory = $this->getModel('Magento\Catalog\Model\Category')->load($id);
			return $this->_currentCategory;
		}
	}
	
	public function getCurrentProduct(){
		if($this->_currentProduct){
			return $this->_currentProduct;
		}else{
			$id = $this->_request->getParam('id');
			$this->_currentProduct = $this->getModel('Magento\Catalog\Model\Product')->load($id);
			return $this->_currentProduct;
		}
	}
	
	public function isCategoryPage(){
		if ($this->_fullActionName == 'catalog_category_view') {
			return true;
		}
		return false;
	}
	
	public function isSearchPage(){
		if ($this->_fullActionName == 'catalogsearch_result_index') {
			return true;
		}
		return false;
	}
	
	public function isProductPage(){
		if ($this->_fullActionName == 'catalog_product_view') {
			return true;
		}
		return false;
	}
	
	public function isPopup(){
		if (
			$this->_fullActionName == 'mgs_quickview_catalog_product_view' || 
			$this->_fullActionName == 'mpanel_edit_section' || 
			$this->_fullActionName == 'mpanel_create_block' || 
			$this->_fullActionName == 'mpanel_create_element' || 
			$this->_fullActionName == 'mpanel_edit_footer' || 
			$this->_fullActionName == 'mpanel_edit_header' || 
			$this->_fullActionName == 'mpanel_edit_staticblock'
		) {
			return true;
		}
		return false;
	}
	public function getCurrentlySelectedCategoryId()
	{
		$params = $this->getModel('Magento\Framework\App\Request\Http')->getParams();
		if (isset($params['cat'])) {
			return $params['cat'];
		}
		return '';
	}
	
	public function getCategories()
	{
		$rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();
		$categoriesArray = $this->_category
			->getCollection()
			->setStoreId($this->_storeManager->getStore()->getId())
			->addAttributeToSelect('*')
			->addAttributeToFilter('is_active', 1)
			->addAttributeToFilter('include_in_menu', 1)
			->addAttributeToFilter('path', array('like' => "1/{$rootCategoryId}/%"))
			->addAttributeToSort('path', 'asc')
			->load()
			->toArray();
		$categories = array();
		if(isset($categoriesArray['items'])){
			foreach ($categoriesArray['items'] as $categoryId => $category) {
				if (isset($category['name'])) {
					$categories[] = array(
						'label' => $category['name'],
						'level' => $category['level'],
						'value' => $category['entity_id']
					);
				}
			}
		}else {
			foreach ($categoriesArray as $categoryId => $category) {
				if (isset($category['name'])) {
					$categories[] = array(
						'label' => $category['name'],
						'level' => $category['level'],
						'value' => $category['entity_id']
					);
				}
			}
		}
		return $categories;
	}
        //check layout current page
        public function checkLayoutPage($category) {
		$settings = $this->_catalogDesign->getDesignSettings($category);		
		return $settings;
	}
}