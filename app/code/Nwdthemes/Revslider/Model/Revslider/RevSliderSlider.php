<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2015 ThemePunch
 */

namespace Nwdthemes\Revslider\Model\Revslider;

use \Nwdthemes\Revslider\Helper\Data;
use \Nwdthemes\Revslider\Helper\Framework;
use \Nwdthemes\Revslider\Helper\Query;
use \Nwdthemes\Revslider\Model\Revslider\ExternalSources\RevSliderFacebook;
use \Nwdthemes\Revslider\Model\Revslider\ExternalSources\RevSliderFlickr;
use \Nwdthemes\Revslider\Model\Revslider\ExternalSources\RevSliderInstagram;
use \Nwdthemes\Revslider\Model\Revslider\ExternalSources\RevSliderTwitter;
use \Nwdthemes\Revslider\Model\Revslider\ExternalSources\RevSliderVimeo;
use \Nwdthemes\Revslider\Model\Revslider\ExternalSources\RevSliderYoutube;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderSlide;
use \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderBase;
use \Nwdthemes\Revslider\Model\Revslider\Framework\PclZip;
use \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderCssParser;
use \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderDB;
use \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderFunctions;
use \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderFunctionsWP;
use \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderPluginUpdate;

class RevSliderSlider extends \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderElementsBase {

    protected $_framework;
    protected $_query;
    protected $_curl;
    protected $_filesystem;
    protected $_images;
    protected $_resource;
    protected $_googleFonts;
    protected $_registerHelper;

    protected static $query;

	const DEFAULT_POST_SORTBY = "ID";
	const DEFAULT_POST_SORTDIR = "DESC";
	
	const VALIDATE_NUMERIC = "numeric";
	const VALIDATE_EMPTY = "empty";
	const FORCE_NUMERIC = "force_numeric";

	const SLIDER_TYPE_GALLERY = "gallery";
	const SLIDER_TYPE_POSTS = "posts";
	const SLIDER_TYPE_TEMPLATE = "template";
	const SLIDER_TYPE_ALL = "all";

	private $slider_version = 5;
	private $id;
	private $title;
	private $alias;
	private $arrParams;
	private $settings;
	private $arrSlides = null;

	public function __construct(
        \Nwdthemes\Revslider\Helper\Framework $framework,
        \Nwdthemes\Revslider\Helper\Query $query,
        \Nwdthemes\Revslider\Helper\Curl $curl,
        \Nwdthemes\Revslider\Helper\Filesystem $filesystem,
        \Nwdthemes\Revslider\Helper\Images $images,
        \Magento\Framework\App\ResourceConnection $resource,
        \Nwdthemes\Revslider\Model\Revslider\GoogleFonts $googleFonts,
        \Nwdthemes\Revslider\Helper\Register $registerHelper
    ) {
        $this->_framework = $framework;
        $this->_query = $query;
        $this->_curl = $curl;
        $this->_filesystem = $filesystem;
        $this->_images = $images;
        $this->_resource = $resource;
        $this->_googleFonts = $googleFonts;
        $this->_registerHelper = $registerHelper;

        self::$query = $this->_query;

        parent::__construct($this->_query, $this->_resource);
	}


	/**
	 *
	 * return if the slider is inited or not
	 */
	public function isInited(){
		if(!empty($this->id))
			return(true);
			
		return(false);
	}
	
	
	/**
	 * 
	 * validate that the slider is inited. if not - throw error
	 */
	private function validateInited(){
		if(empty($this->id))
			RevSliderFunctions::throwError("The slider is not initialized!");
	}
	
	/**
	 * init slider by db data
	 */
	public function initByDBData($arrData){
		
		$this->id = $arrData["id"];
		$this->title = $arrData["title"];
		$this->alias = $arrData["alias"];
		
		$settings = isset($arrData["settings"]) ? $arrData["settings"] : '';
		$settings = (array)json_decode($settings);
		
		$this->settings = $settings;
		
		$params = isset($arrData["params"]) ? $arrData["params"] : '';
		$params = (array)json_decode($params);
		$params = RevSliderBase::translate_settings_to_v5($params);
		
		$this->arrParams = $params;
	}

	
	/**
	 *
	 * init the slider object by database id
	 */
	public function initByID($sliderID){
		RevSliderFunctions::validateNumeric($sliderID,"Slider ID");
		
		try{
            $sliderData = $this->db->fetchSingle(RevSliderGlobals::$table_sliders, $this->db->prepare("id = %s", array($sliderID)));
		}catch(\Exception $e){
            Data::logException($e);
			$message = $e->getMessage();
			echo $message;
			exit;
		}

		$this->initByDBData($sliderData);
	}

	/**
	 *
	 * init slider by alias
	 */
	public function initByAlias($alias){

		try{
            $where = $this->db->prepare("`alias` = %s AND ((`type` IS NULL) OR (`type` != 'template'))", array($alias));
			$sliderData = $this->db->fetchSingle(RevSliderGlobals::$table_sliders,$where);
		}catch(\Exception $e){
            Data::logException($e);
			$arrAliases = $this->getAllSliderAliases();
			$strAliases = "";

            if(!empty($arrAliases) && is_array($arrAliases)){
				$arrAliases = array_slice($arrAliases, 0, 6); //show 6 other, will be enough

                $strAliases = "'".$this->_framework->sanitize_text_field(implode("' or '", $arrAliases))."'";
			}

            $errorMessage = 'Slider with alias <strong>'.$this->_framework->sanitize_text_field($this->_framework->esc_attr($alias)).'</strong> not found.';
			if(!empty($strAliases))
				$errorMessage .= ' <br>Maybe you mean: '.$strAliases;
				
			RevSliderFunctions::throwError($errorMessage);
		}
		$this->initByDBData($sliderData);
	}

	
	/**
	 * 
	 * init by id or alias
	 */
	public function initByMixed($mixed){
        if (is_numeric($mixed) && ! (is_string($mixed) && self::isAliasExists($mixed)))
			$this->initByID($mixed);
		else
			$this->initByAlias($mixed);
	}
	

	/**
	 * 
	 * get data functions
	 */
	public function getTitle(){
		return($this->title);
	}

	public function getID(){
		return($this->id);
	}
	
	public function getParams(){
		return($this->arrParams);
	}
	
	/*
	 * return Slider settings
	 * @since: 5.0
	 */
	public function getSettings(){
		return($this->settings);
	}
	
	/*
	 * return true if slider is favorite
	 * @since: 5.0
	 */
	public function isFavorite(){
		if(!empty($this->settings)){
			if(isset($this->settings['favorite']) && $this->settings['favorite'] == 'true') return true;
		}
		
		return false;
	}

	/**
	 * 
	 * set slider params
	 */
	public function setParams($arrParams){
		$this->arrParams = $arrParams;
	}
	
	
	/**
     * set specific slider param
     * @since: 5.1.1
     */
    public function setParam($param, $value){
        $this->arrParams[$param] = $value;
    }
    
    
    /**
	 * 
	 * get parameter from params array. if no default, then the param is a must!
	 */
	function getParam($name,$default=null,$validateType = null,$title=""){
		
		if($default === null){
			$default = "";
		}

		$value = RevSliderFunctions::getVal($this->arrParams, $name,$default);
		
		//validation:
		switch($validateType){
			case self::VALIDATE_NUMERIC:
			case self::VALIDATE_EMPTY:
				$paramTitle = !empty($title)?$title:$name;
				if($value !== "0" && $value !== 0 && empty($value))
					RevSliderFunctions::throwError("The param <strong>$paramTitle</strong> should not be empty.");
			break;
			case self::VALIDATE_NUMERIC:
				$paramTitle = !empty($title)?$title:$name;
				if(!is_numeric($value))
					RevSliderFunctions::throwError("The param <strong>$paramTitle</strong> should be numeric. Now it's: $value");
			break;
			case self::FORCE_NUMERIC:
				if(!is_numeric($value)){
					$value = 0;
					if(!empty($default))
						$value = $default;
				}
			break; 
		}
		
		return $value;
	}
	
	public function getAlias(){
		return($this->alias);
	}
	
	/**
	 * get combination of title (alias)
	 */
	public function getShowTitle(){
		$showTitle = $this->title;
		return($showTitle);
	}
	
	/**
	 * 
	 * get slider shortcode
	 */
	public function getShortcode(){
		$shortCode = '[rev_slider alias="'.$this->alias.'"]';
		return($shortCode);
	}
	
	
	/**
	 * 
	 * check if alias exists in DB
	 */
	public function isAliasExistsInDB($alias){

        $where = $this->db->prepare("alias = %s ", array($alias));
		if(!empty($this->id)){

            $where .= $this->db->prepare(" and id != %s  AND `type` != 'template'", array($this->id));
		}
		

		$response = $this->db->fetch(RevSliderGlobals::$table_sliders,$where);

		return(!empty($response));
		
	}


	/**
	 *
	 * check if alias exists in DB
	 */
	public static function isAliasExists($alias){
		$wpdb = self::$query;

		$response = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".RevSliderGlobals::$table_sliders." WHERE alias = %s AND `type` != 'template'", $alias));

		return(!empty($response));
	}


	/**
	 *
	 * validate settings for add
	 */
	private function validateInputSettings($title,$alias,$params){
		RevSliderFunctions::validateNotEmpty($title,"title");
		RevSliderFunctions::validateNotEmpty($alias,"alias");
		
		if($this->isAliasExistsInDB($alias))
			RevSliderFunctions::throwError("Some other slider with alias '$alias' already exists");
		
	}

	
	/**
	 * set new hero slide id for the Slider
	 * @since: 5.0
	 */
	public function setHeroSlide($data){
		$sliderID = RevSliderFunctions::getVal($data, "slider_id");
		RevSliderFunctions::validateNotEmpty($sliderID,"Slider ID");
		$this->initByID($sliderID);

		$new_slide_id = RevSliderFunctions::getVal($data, "slide_id");
		RevSliderFunctions::validateNotEmpty($new_slide_id,"Hero Slide ID");
		
		$this->updateParam(array('hero_active' => intval($new_slide_id)));
		
		return($new_slide_id);
	}
	
	/**
	 * 
	 * create / update slider from options
	 */
	private function createUpdateSliderFromOptions($options, $sliderID = null){
		
		$arrMain = RevSliderFunctions::getVal($options, "main");
		$params = RevSliderFunctions::getVal($options, "params");
		
		//trim all input data
		$arrMain = RevSliderFunctions::trimArrayItems($arrMain);
		
		$params = RevSliderFunctions::trimArrayItems($params);
		
		$params = array_merge($arrMain,$params);
		
        $title = $this->_framework->sanitize_text_field(RevSliderFunctions::getVal($arrMain, "title"));
        $alias = $this->_framework->sanitize_text_field(RevSliderFunctions::getVal($arrMain, "alias"));
        
        //params css and js check
        if(!RevSliderFunctionsWP::isAdminUser() && $this->_framework->apply_filters('revslider_restrict_role', true)){
            //dont allow css and javascript from users other than administrator
            unset($params['custom_css']);
            unset($params['custom_javascript']);
        }
		
        if(!empty($sliderID)){
			$this->initByID($sliderID);
			
            if(!RevSliderFunctionsWP::isAdminUser() && $this->_framework->apply_filters('revslider_restrict_role', true)){
                //check for js and css, add it to $params
                $params['custom_css'] = $this->getParam('custom_css', '');
                $params['custom_javascript'] = $this->getParam('custom_javascript', '');
            }
            
        }
        
		$this->validateInputSettings($title, $alias, $params);
		
		$jsonParams = json_encode($params);

		//insert slider to database
		$arrData = array();
		$arrData["title"] = $title;
		$arrData["alias"] = $alias;
		$arrData["params"] = $jsonParams;
        $arrData["type"] = '';

		if(empty($sliderID)){	//create slider	
			
			$arrData['settings'] = json_encode(array('version' => 5.0));
			
			$sliderID = $this->db->insert(RevSliderGlobals::$table_sliders,$arrData);
			return($sliderID);
			
		}else{	//update slider
			$this->initByID($sliderID);
			
			$settings = $this->getSettings();
			$settings['version'] = 5.0;
			$arrData['settings'] = json_encode($settings);
			
            $sliderID = $this->db->update(RevSliderGlobals::$table_sliders,$arrData,array("id"=>$sliderID));
		}
	}
	
	
	/**
	 * delete slider from datatase
	 */
	public function deleteSlider(){
		
		$this->validateInited();
		
		//delete slider
        $this->db->delete(RevSliderGlobals::$table_sliders, $this->db->prepare("id = %s", array($this->id)));
		
		//delete slides
		$this->deleteAllSlides();
		$this->deleteStaticSlide();
	}

	/**
	 * 
	 * delete all slides
	 */
	private function deleteAllSlides(){
		$this->validateInited();
		
        $this->db->delete(RevSliderGlobals::$table_slides, $this->db->prepare("slider_id = %s", array($this->id)));

        $this->_framework->do_action('revslider_slider_deleteAllSlides', $this->id);
	}
	

	/**
	 * 
	 * delete all slides
	 */
	public function deleteStaticSlide(){
		$this->validateInited();
		
        $this->db->delete(RevSliderGlobals::$table_static_slides, $this->db->prepare("slider_id = %s", array($this->id)));
	}
	
	
	/**
	 * 
	 * get all slide children
	 */
	public function getArrSlideChildren($slideID){
	
		$this->validateInited();
		$arrSlides = $this->getSlidesFromGallery();
		if(!isset($arrSlides[$slideID]))
			RevSliderFunctions::throwError("Slide with id: $slideID not found in the main slides of the slider. Maybe it's child slide.");
		
		$slide = $arrSlides[$slideID];
		$arrChildren = $slide->getArrChildren();
		
		return($arrChildren);
	}
	
	
	/**
	 * 
	 * duplicate slider in datatase
	 */
    private function duplicateSlider($title = false, $prefix = false){
		
		$this->validateInited();
		
        //insert a new slider
        $sqlSelect = $this->db->prepare("select ".RevSliderGlobals::FIELDS_SLIDER." from ".RevSliderGlobals::$table_sliders." where id = %s", array($this->id));
        $sqlInsert = "insert into ".RevSliderGlobals::$table_sliders." (".RevSliderGlobals::FIELDS_SLIDER.") ($sqlSelect)";

        $this->db->runSql($sqlInsert);
        $lastID = $this->db->getLastInsertID();
        RevSliderFunctions::validateNotEmpty($lastID);


        $params = $this->arrParams;

		if($title === false){
			//get slider number:
			$response = $this->db->fetch(RevSliderGlobals::$table_sliders);
			$numSliders = count($response);
			$newSliderSerial = $numSliders+1;
			
			$newSliderTitle = "Slider".$newSliderSerial;
			$newSliderAlias = "slider".$newSliderSerial;
		}else{
            if($prefix !== false){
                $newSliderTitle = $this->_framework->sanitize_text_field($title.' '.$params['title']);
                $newSliderAlias = $this->_framework->sanitize_title_with_dashes($title.' '.$params['title']);
            }else{
                $newSliderTitle = $this->_framework->sanitize_text_field($title);
                $newSliderAlias = $this->_framework->sanitize_title_with_dashes($title);
            }
			// Check Duplicate Alias
            $sqlTitle = $this->db->fetch(RevSliderGlobals::$table_sliders, $this->db->prepare("alias = %s", array($this->_framework->sanitize_title($title))));
			if(!empty($sqlTitle)){
				$response = $this->db->fetch(RevSliderGlobals::$table_sliders);
				$numSliders = count($response);
				$newSliderSerial = $numSliders+1;
				$newSliderTitle .= $newSliderSerial;
				$newSliderAlias .= $newSliderSerial;	
			}
		}
		
        //update params

        $params["title"] = $newSliderTitle;
        $params["alias"] = $newSliderAlias;
        $params["shortcode"] = "{{rev_slider alias=\"". $newSliderAlias ."\"}}";
		
		//update the new slider with the title and the alias values
		$arrUpdate = array();
		$arrUpdate["title"] = $newSliderTitle;
		$arrUpdate["alias"] = $newSliderAlias;
		


		$jsonParams = json_encode($params);
		$arrUpdate["params"] = $jsonParams;

		$arrUpdate["type"] = '';//remove the type as we do not want it to be template if it was

		$this->db->update(RevSliderGlobals::$table_sliders, $arrUpdate, array("id"=>$lastID));
		
		//duplicate Slides
        $slides = $this->db->fetch(RevSliderGlobals::$table_slides, $this->db->prepare("slider_id = %s", array($this->id)));
		if(!empty($slides)){
			foreach($slides as $slide){
				$slide['slider_id'] = $lastID;
				$myID = $slide['id'];
				unset($slide['id']);
				$last_id = $this->db->insert(RevSliderGlobals::$table_slides,$slide);

				if(isset($myID)){
					$slider_map[$myID] = $last_id;
				}
			}
		}
		
		//duplicate static slide if exists
		$slide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
		$staticID = $slide->getStaticSlideID($this->id);
		$static_id = 0;
		if($staticID !== false){
            $record = $this->db->fetchSingle(RevSliderGlobals::$table_static_slides, $this->db->prepare("id = %s", array($staticID)));
			unset($record['id']);
			$record['slider_id'] = $lastID;

			$static_id = $this->db->insert(RevSliderGlobals::$table_static_slides, $record);
		}
		
		
		//update actions
        $slides = $this->db->fetch(RevSliderGlobals::$table_slides, $this->db->prepare("slider_id = %s", array($lastID)));
		if($static_id > 0){
            $slides_static = $this->db->fetch(RevSliderGlobals::$table_static_slides, $this->db->prepare("id = %s", array($static_id)));
			$slides = array_merge($slides, $slides_static);
		}
		if(!empty($slides)){
			foreach($slides as $slide){
				$c_slide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
				$c_slide->initByData($slide);
				
				$layers = $c_slide->getLayers();
				$did_change = false;
				foreach($layers as $key => $value){
					if(isset($value['layer_action'])){
						if(isset($value['layer_action']->jump_to_slide) && !empty($value['layer_action']->jump_to_slide)){
							foreach($value['layer_action']->jump_to_slide as $jtsk => $jtsval){
								if(isset($slider_map[$jtsval])){
									
									$layers[$key]['layer_action']->jump_to_slide[$jtsk] = $slider_map[$jtsval];
									$did_change = true;
								}
							}
						}
					}
				}
				
				if($did_change === true){
					
					$arrCreate = array();
					$my_layers = json_encode($layers);
					if(empty($my_layers))
						$my_layers = stripslashes(json_encode($layers));
					
					$arrCreate['layers'] = $my_layers;
					
					if($slide['id'] == $static_id){
						$this->db->update(RevSliderGlobals::$table_static_slides,$arrCreate,array("id"=>$static_id));
					}else{
						$this->db->update(RevSliderGlobals::$table_slides,$arrCreate,array("id"=>$slide['id']));
					}
					
				}
			}
		}
		
		//change the javascript api ID to the correct one
		$c_slider = new RevSliderSlider($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
		$c_slider->initByID($lastID);
		
		$cus_js = $c_slider->getParam('custom_javascript', '');
		
		if(strpos($cus_js, 'revapi') !== false){
			if(preg_match_all('/revapi[0-9]*/', $cus_js, $results)){

				if(isset($results[0]) && !empty($results[0])){
					foreach($results[0] as $replace){
						$cus_js = str_replace($replace, 'revapi'.$lastID, $cus_js);
					}
				}
				
				$c_slider->updateParam(array('custom_javascript' => $cus_js));
				
			}
		}

        return $lastID;
	}
	
	
	/**
	 * duplicate slide
	 */
	public function duplicateSlide($slideID){
		$slide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
		$slide->initByID($slideID);
		$order = $slide->getOrder();
		$slides = $this->getSlidesFromGallery();
		$newOrder = $order+1;
		$this->shiftOrder($newOrder);
		
		//do duplication
        $sqlSelect = $this->db->prepare("select ".RevSliderGlobals::FIELDS_SLIDE." from ".RevSliderGlobals::$table_slides." where id = %s", array(intval($slideID)));
		$sqlInsert = "insert into ".RevSliderGlobals::$table_slides." (".RevSliderGlobals::FIELDS_SLIDE.") ($sqlSelect)";
		
		$this->db->runSql($sqlInsert);
		$lastID = $this->db->getLastInsertID();
		RevSliderFunctions::validateNotEmpty($lastID);
		
		//update order
		$arrUpdate = array("slide_order"=>$newOrder);
		
		$this->db->update(RevSliderGlobals::$table_slides,$arrUpdate, array("id"=>$lastID));
		
		return($lastID);
	}
	
	
	/**
	 * 
	 * copy / move slide
	 */		
	private function copyMoveSlide($slideID,$targetSliderID,$operation){
		
		if($operation == "move"){
			
			$targetSlider = new RevSliderSlider($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
			$targetSlider->initByID($targetSliderID);
			$maxOrder = $targetSlider->getMaxOrder();
			$newOrder = $maxOrder+1;
			$arrUpdate = array("slider_id"=>$targetSliderID,"slide_order"=>$newOrder);	
			
			//update children
			$arrChildren = $this->getArrSlideChildren($slideID);
			foreach($arrChildren as $child){
				$childID = $child->getID();
				$this->db->update(RevSliderGlobals::$table_slides,$arrUpdate,array("id"=>$childID));
			}
			
			$this->db->update(RevSliderGlobals::$table_slides,$arrUpdate,array("id"=>$slideID));
			
		}else{	//in place of copy
			$newSlideID = $this->duplicateSlide($slideID);
			$this->duplicateChildren($slideID, $newSlideID);
			
			$this->copyMoveSlide($newSlideID,$targetSliderID,"move");
		}
	}
	
	
	/**
	 * 
	 * shift order of the slides from specific order
	 */
	private function shiftOrder($fromOrder){
		
        $where = $this->db->prepare(" slider_id = %s and slide_order >= %s", array($this->id, $fromOrder));
		$sql = "update ".RevSliderGlobals::$table_slides." set slide_order=(slide_order+1) where $where";
		$this->db->runSql($sql);
		
	}
	
	
	/**
	 * 
	 * create slider in database from options
	 */
	public function createSliderFromOptions($options){
		$sliderID = $this->createUpdateSliderFromOptions($options,null);
		return($sliderID);			
	}
	
	
	/**
	 * 
	 * export slider from data, output a file for download
	 */
	public function exportSlider($useDummy = false){
		
		$this->validateInited();
		
		$sliderParams = $this->getParamsForExport();
		$arrSlides = $this->getSlidesForExport($useDummy);
		$arrStaticSlide = $this->getStaticSlideForExport($useDummy);
		
		$usedCaptions = array();
		$usedAnimations = array();
		$usedImages = array();
        $usedSVG = array();
		$usedVideos = array();
        $usedNavigations = array();
		
		$cfw = array();
		if(!empty($arrSlides) && count($arrSlides) > 0) $cfw = array_merge($cfw, $arrSlides);
		if(!empty($arrStaticSlide) && count($arrStaticSlide) > 0) $cfw = array_merge($cfw, $arrStaticSlide);


        //remove image_id as it is not needed in export
        //plus remove background image if solid color or transparent
        if(!empty($arrSlides)){
            foreach($arrSlides as $k => $s){
                if(isset($arrSlides[$k]['params']['image_id'])) unset($arrSlides[$k]['params']['image_id']);
                if(isset($arrSlides[$k]['params']["background_type"]) && ($arrSlides[$k]['params']["background_type"] == 'solid' || $arrSlides[$k]['params']["background_type"] == "trans" || $arrSlides[$k]['params']["background_type"] == "transparent")){
                    if(isset($arrSlides[$k]['params']['background_image']))
                        $arrSlides[$k]['params']['background_image'] = '';
                }
            }
        }
        if(!empty($arrStaticSlide)){
            foreach($arrStaticSlide as $k => $s){
                if(isset($arrStaticSlide[$k]['params']['image_id'])) unset($arrStaticSlide[$k]['params']['image_id']);
                if(isset($arrStaticSlide[$k]['params']["background_type"]) && ($arrStaticSlide[$k]['params']["background_type"] == 'solid' || $arrStaticSlide[$k]['params']["background_type"] == "trans" || $arrStaticSlide[$k]['params']["background_type"] == "transparent")){
                    if(isset($arrStaticSlide[$k]['params']['background_image']))
                        $arrStaticSlide[$k]['params']['background_image'] = '';
                }
            }
        }

		if(!empty($cfw) && count($cfw) > 0){
			foreach($cfw as $key => $slide){
                //check if we are transparent and so on

				if(isset($slide['params']['image']) && $slide['params']['image'] != '') $usedImages[$slide['params']['image']] = true; //['params']['image'] background url
				if(isset($slide['params']['background_image']) && $slide['params']['background_image'] != '') $usedImages[$slide['params']['background_image']] = true; //['params']['image'] background url
				if(isset($slide['params']['slide_thumb']) && $slide['params']['slide_thumb'] != '') $usedImages[$slide['params']['slide_thumb']] = true; //['params']['image'] background url
				
				//html5 video
				if(isset($slide['params']['background_type']) && $slide['params']['background_type'] == 'html5'){
					if(isset($slide['params']['slide_bg_html_mpeg']) && $slide['params']['slide_bg_html_mpeg'] != '') $usedVideos[$slide['params']['slide_bg_html_mpeg']] = true;
					if(isset($slide['params']['slide_bg_html_webm']) && $slide['params']['slide_bg_html_webm'] != '') $usedVideos[$slide['params']['slide_bg_html_webm']] = true;
					if(isset($slide['params']['slide_bg_html_ogv']) && $slide['params']['slide_bg_html_ogv'] != '') $usedVideos[$slide['params']['slide_bg_html_ogv']] = true;
				}else{
					if(isset($slide['params']['slide_bg_html_mpeg']) && $slide['params']['slide_bg_html_mpeg'] != '') $slide['params']['slide_bg_html_mpeg'] = '';
					if(isset($slide['params']['slide_bg_html_webm']) && $slide['params']['slide_bg_html_webm'] != '') $slide['params']['slide_bg_html_webm'] = '';
					if(isset($slide['params']['slide_bg_html_ogv']) && $slide['params']['slide_bg_html_ogv'] != '') $slide['params']['slide_bg_html_ogv'] = '';
				}

				//image thumbnail
				if(isset($slide['layers']) && !empty($slide['layers']) && count($slide['layers']) > 0){
					foreach($slide['layers'] as $lKey => $layer){
						if(isset($layer['style']) && $layer['style'] != '') $usedCaptions[$layer['style']] = true;
						if(isset($layer['animation']) && $layer['animation'] != '' && strpos($layer['animation'], 'customin') !== false) $usedAnimations[str_replace('customin-', '', $layer['animation'])] = true;
						if(isset($layer['endanimation']) && $layer['endanimation'] != '' && strpos($layer['endanimation'], 'customout') !== false) $usedAnimations[str_replace('customout-', '', $layer['endanimation'])] = true;
						if(isset($layer['image_url']) && $layer['image_url'] != '') $usedImages[$layer['image_url']] = true; //image_url if image caption
                        if(isset($layer['bgimage_url']) && $layer['bgimage_url'] != '') $usedImages[$layer['bgimage_url']] = true; //image_url if background layer image

                        if(isset($layer['type']) && ($layer['type'] == 'video' || $layer['type'] == 'audio')){
							
							$video_data = (isset($layer['video_data'])) ? (array) $layer['video_data'] : array();
							
							if(!empty($video_data) && isset($video_data['video_type']) && $video_data['video_type'] == 'html5'){

								if(isset($video_data['urlPoster']) && $video_data['urlPoster'] != '') $usedImages[$video_data['urlPoster']] = true;
								
								if(isset($video_data['urlMp4']) && $video_data['urlMp4'] != '') $usedVideos[$video_data['urlMp4']] = true;
								if(isset($video_data['urlWebm']) && $video_data['urlWebm'] != '') $usedVideos[$video_data['urlWebm']] = true;
								if(isset($video_data['urlOgv']) && $video_data['urlOgv'] != '') $usedVideos[$video_data['urlOgv']] = true;
								
							}elseif(!empty($video_data) && isset($video_data['video_type']) && $video_data['video_type'] != 'html5'){ //video cover image
                                if($video_data['video_type'] == 'audio'){
                                    if(isset($video_data['urlAudio']) && $video_data['urlAudio'] != '') $usedVideos[$video_data['urlAudio']] = true;
                                }else{
                                    if(isset($video_data['previewimage']) && $video_data['previewimage'] != '') $usedImages[$video_data['previewimage']] = true;
                                }
                            }
						
                            if($video_data['video_type'] != 'html5'){
                                $video_data['urlMp4'] = '';
                                $video_data['urlWebm'] = '';
                                $video_data['urlOgv'] = '';
                            }
                            if($video_data['video_type'] != 'audio'){
                                $video_data['urlAudio'] = '';
                            }
							if(isset($layer['video_image_url']) && $layer['video_image_url'] != '') $usedImages[$layer['video_image_url']] = true;
                        }

                        if(isset($layer['type']) && $layer['type'] == 'svg'){
                            if(isset($layer['svg']) && isset($layer['svg']->src)){
                                $usedSVG[$layer['svg']->src] = true;
                            }
                        }
                    }
                }
            }

			$d = array('usedSVG' => $usedSVG, 'usedImages' => $usedImages, 'usedVideos' => $usedVideos);
			$d = $this->_framework->apply_filters('revslider_exportSlider_usedMedia', $d, $cfw, $sliderParams, $useDummy); //  $arrSlides, $arrStaticSlide, 
			
			$usedSVG = $d['usedSVG'];
			$usedImages = $d['usedImages'];
			$usedVideos = $d['usedVideos'];
        }
		
		$arrSliderExport = array("params"=>$sliderParams,"slides"=>$arrSlides);
		if(!empty($arrStaticSlide))
			$arrSliderExport['static_slides'] = $arrStaticSlide;
		
		$strExport = serialize($arrSliderExport);
		
		//$strExportAnim = serialize(RevSliderOperations::getFullCustomAnimations());
		
        $exportname = (!empty($this->alias)) ? $this->alias.'.zip' : "slider_export.zip";
        
        //add navigations if not default animation
        if(isset($sliderParams['navigation_arrow_style'])) $usedNavigations[$sliderParams['navigation_arrow_style']] = true;
        if(isset($sliderParams['navigation_bullets_style'])) $usedNavigations[$sliderParams['navigation_bullets_style']] = true;
        if(isset($sliderParams['thumbnails_style'])) $usedNavigations[$sliderParams['thumbnails_style']] = true;
        if(isset($sliderParams['tabs_style'])) $usedNavigations[$sliderParams['tabs_style']] = true;
        $navs = false;
        if(!empty($usedNavigations)){
            $navs = RevSliderNavigation::export_navigation($usedNavigations);
            if($navs !== false) $navs = serialize($navs);
        }
		
		
		$styles = '';
		if(!empty($usedCaptions)){
			$captions = array();
			foreach($usedCaptions as $class => $val){
				$cap = RevSliderOperations::getCaptionsContentArray($class);
				//set also advanced styles here...
				if(!empty($cap))
					$captions[] = $cap;
			}
			$styles = RevSliderCssParser::parseArrayToCss($captions, "\n", true);
		}
		
		$animations = '';
		if(!empty($usedAnimations)){
			$animation = array();
			foreach($usedAnimations as $anim => $val){
				$anima = RevSliderOperations::getFullCustomAnimationByID($anim);
                if($anima !== false) $animation[] = $anima;
				
			}
			if(!empty($animation)) $animations = serialize($animation);
		}
		
		$usedImages = array_merge($usedImages, $usedVideos);

		$usepcl = false;
		if (class_exists('\ZipArchive', false)) {
			$zip = new \ZipArchive();
			$success = $zip->open(RevSliderGlobals::$uploadsUrlExportZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
			
			if($success !== true)
				throwError("Can't create zip file: ".RevSliderGlobals::$uploadsUrlExportZip);
			
		}else{
			$pclzip = new PclZip(RevSliderGlobals::$uploadsUrlExportZip);
			//either the function uses die() or all is cool
			$usepcl = true;
		}

        //add svg to the zip
        if(!empty($usedSVG)){

            $content_url_front = $this->_framework->getAssetUrl('', array('_area' => 'frontend')) . '/';
            $content_url_admin = $this->_framework->getAssetUrl('', array('_area' => 'backend')) . '/';
            $content_path = $this->_framework->getAssetPath('', array('_area' => 'backend')) . DIRECTORY_SEPARATOR;

            $ud = $this->_framework->wp_upload_dir();
            $media_url = rtrim($ud['baseurl'], '/');
            $media_path = $ud['basedir'];

            foreach($usedSVG as $file => $val){
                if(strpos($file, 'http') !== false){ //remove all up to wp-content folder
                    $checkpath = str_replace(array($content_url_front, $content_url_admin), '', $file);
                    if ($checkpath !== $file) {
                        if (is_file($content_path.$checkpath)) {
                            $strExport = str_replace($file, 'revslider/'.$checkpath, $strExport);
                        }
                    } else {
                        $checkpath = str_replace($media_url, '', $file);
                        if (is_file($media_path.$checkpath)) {
                            $strExport = str_replace($file, $checkpath, $strExport);
                        }
                    }

                }
            }
        }
		
		//add images to zip
		if(!empty($usedImages)){
			$upload_dir = RevSliderFunctionsWP::getPathUploads();
			$upload_dir_multisiteless = $this->_framework->wp_upload_dir();
			$cont_url = $upload_dir_multisiteless['baseurl'];
			$cont_url_no_www = str_replace('www.', '', $upload_dir_multisiteless['baseurl']);
			$upload_dir_multisiteless = $upload_dir_multisiteless['basedir'].'/';

			foreach($usedImages as $file => $val){

				if($useDummy == "true"){ //only use dummy images

				}else{ //use the real images
					if(strpos($file, 'http') !== false){
                        //check if we are in objects folder, if yes take the original image into the zip-

                        $remove = false;
						$checkpath = str_replace(array($cont_url, $cont_url_no_www), '', $this->_images->imageClean($file));

						if(is_file($upload_dir.$checkpath)){
							if(!$usepcl){
								$zip->addFile($upload_dir.$checkpath, 'images'.$checkpath);
							}else{
								$v_list = $pclzip->add($upload_dir.$checkpath, PCLZIP_OPT_REMOVE_PATH, $upload_dir, PCLZIP_OPT_ADD_PATH, 'images/');
							}
							$remove = true;
						}elseif(is_file($upload_dir_multisiteless.$checkpath)){
							if(!$usepcl){
								$zip->addFile($upload_dir_multisiteless.$checkpath, 'images'.$checkpath);
							}else{
								$v_list = $pclzip->add($upload_dir_multisiteless.$checkpath, PCLZIP_OPT_REMOVE_PATH, $upload_dir_multisiteless, PCLZIP_OPT_ADD_PATH, 'images/');
							}
							$remove = true;
						}
						
						if($remove){ //as its http, remove this from strexport
							$strExport = str_replace(array($cont_url.$checkpath, $cont_url_no_www.$checkpath), $checkpath, $strExport);
						}
					}else{
						if(is_file($upload_dir.$file)){
							if(!$usepcl){
								$zip->addFile($upload_dir.$file, 'images/'.$file);
							}else{
								$v_list = $pclzip->add($upload_dir.$file, PCLZIP_OPT_REMOVE_PATH, $upload_dir, PCLZIP_OPT_ADD_PATH, 'images/');
							}
						}elseif(is_file($upload_dir_multisiteless.$file)){
							if(!$usepcl){
								$zip->addFile($upload_dir_multisiteless.$file, 'images/'.$file);
							}else{
								$v_list = $pclzip->add($upload_dir_multisiteless.$file, PCLZIP_OPT_REMOVE_PATH, $upload_dir_multisiteless, PCLZIP_OPT_ADD_PATH, 'images/');
							}
						}
					}
				}
			}
		}
		
		if(!$usepcl){
			$zip->addFromString("slider_export.txt", $strExport); //add slider settings
		}else{
			$list = $pclzip->add(array(array( PCLZIP_ATT_FILE_NAME => 'slider_export.txt',PCLZIP_ATT_FILE_CONTENT => $strExport)));
			if ($list == 0) { die("ERROR : '".$pclzip->errorInfo(true)."'"); }
		}
		if(strlen(trim($animations)) > 0){
			if(!$usepcl){
				$zip->addFromString("custom_animations.txt", $animations); //add custom animations
			}else{
				$list = $pclzip->add(array(array( PCLZIP_ATT_FILE_NAME => 'custom_animations.txt',PCLZIP_ATT_FILE_CONTENT => $animations)));
				if ($list == 0) { die("ERROR : '".$pclzip->errorInfo(true)."'"); }
			}
		}
		if(strlen(trim($styles)) > 0){
			if(!$usepcl){
				$zip->addFromString("dynamic-captions.css", $styles); //add dynamic styles
			}else{
				$list = $pclzip->add(array(array( PCLZIP_ATT_FILE_NAME => 'dynamic-captions.css',PCLZIP_ATT_FILE_CONTENT => $styles)));
				if ($list == 0) { die("ERROR : '".$pclzip->errorInfo(true)."'"); }
			}
		}
        if(strlen(trim($navs)) > 0){
            if(!$usepcl){
                $zip->addFromString("navigation.txt", $navs); //add dynamic styles
            }else{
                $list = $pclzip->add(array(array( PCLZIP_ATT_FILE_NAME => 'navigation.txt',PCLZIP_ATT_FILE_CONTENT => $navs)));
                if ($list == 0) { die("ERROR : '".$pclzip->errorInfo(true)."'"); }
            }
        }
		
		$static_css = RevSliderOperations::getStaticCss();
		if(trim($static_css) !== ''){
			if(!$usepcl){
				$zip->addFromString("static-captions.css", $static_css); //add slider settings
			}else{
				$list = $pclzip->add(array(array( PCLZIP_ATT_FILE_NAME => 'static-captions.css',PCLZIP_ATT_FILE_CONTENT => $static_css)));
				if ($list == 0) { die("ERROR : '".$pclzip->errorInfo(true)."'"); }
			}
		}
		$enable_slider_pack = $this->_framework->apply_filters('revslider_slider_pack_export', false);
		
		if($enable_slider_pack){ //allow for slider packs the automatic creation of the info.cfg
			if(!$usepcl){
				$zip->addFromString('info.cfg', md5($this->alias)); //add slider settings
			}else{
				$list = $pclzip->add(array(array( PCLZIP_ATT_FILE_NAME => 'info.cfg',PCLZIP_ATT_FILE_CONTENT => md5($this->alias))));
				if ($list == 0) { die("ERROR : '".$pclzip->errorInfo(true)."'"); }
			}
		}
		
		if(!$usepcl){
			$zip->close();
		}else{
			//do nothing
		}
		
		
		header("Content-type: application/zip");
		header("Content-Disposition: attachment; filename=".$exportname);
		header("Pragma: no-cache");
		header("Expires: 0");
		readfile(RevSliderGlobals::$uploadsUrlExportZip);
		
		@unlink(RevSliderGlobals::$uploadsUrlExportZip); //delete file after sending it to user
		
		exit();
	}
	
	
	/**
	 * import slider from multipart form
	 * @since: 5.3.1: $updateStatic is now deprecated
	 */
    public function importSliderFromPost($updateAnim = true, $updateStatic = true, $exactfilepath = false, $is_template = false, $single_slide = false, $updateNavigation = true){

        $real_slider_id = '';

        try{
			$upload_dir = $this->_framework->wp_upload_dir();
			$rem_path = $upload_dir['basedir'].'/rstemp/';
			$d_path = $rem_path;
			
			$sliderID = RevSliderFunctions::getPostVariable("sliderid");
			$sliderExists = !empty($sliderID);
			
			if($sliderExists)
				$this->initByID($sliderID);
			
			if($exactfilepath !== false){
				$filepath = $exactfilepath;
			}else{
				switch ($_FILES['import_file']['error']) {
					case UPLOAD_ERR_OK:
						break;
					case UPLOAD_ERR_NO_FILE:
						RevSliderFunctions::throwError(__('No file sent.'));
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						RevSliderFunctions::throwError(__('Exceeded filesize limit.'));
					default:
					break;
				}
				$filepath = $_FILES["import_file"]["tmp_name"];
			}
			
			if(file_exists($filepath) == false)
				RevSliderFunctions::throwError(__('Import file not found!!!'));
			
			$importZip = false;
			
			$wp_filesystem = $this->_filesystem->WP_Filesystem();

			$unzipfile = $this->_filesystem->unzip_file( $filepath, $d_path);

            if( $this->_framework->is_wp_error($unzipfile) ){
				$d_path = Framework::$RS_PLUGIN_PATH.'rstemp/';
				$unzipfile = $this->_filesystem->unzip_file( $filepath, $d_path);

				if( $this->_framework->is_wp_error($unzipfile) ){
					$f = basename($filepath);
					$d_path = str_replace($f, '', $filepath);
                    $rem_path = $d_path;

					$unzipfile = $this->_filesystem->unzip_file( $filepath, $d_path);
				}
            }

            if( !$this->_framework->is_wp_error($unzipfile) ){
				$importZip = true; //raus damit..
				
				//read all files needed
				$content = ( $wp_filesystem->exists( $d_path.'slider_export.txt' ) ) ? $wp_filesystem->get_contents( $d_path.'slider_export.txt' ) : '';
				if($content == ''){
					RevSliderFunctions::throwError(__('slider_export.txt does not exist!'));
				}
				$animations = ( $wp_filesystem->exists( $d_path.'custom_animations.txt' ) ) ? $wp_filesystem->get_contents( $d_path.'custom_animations.txt' ) : '';
				$dynamic = ( $wp_filesystem->exists( $d_path.'dynamic-captions.css' ) ) ? $wp_filesystem->get_contents( $d_path.'dynamic-captions.css' ) : '';
                $navigations = ( $wp_filesystem->exists( $d_path.'navigation.txt' ) ) ? $wp_filesystem->get_contents( $d_path.'navigation.txt' ) : '';
				
				$uid_check = ( $wp_filesystem->exists( $d_path.'info.cfg' ) ) ? $wp_filesystem->get_contents( $d_path.'info.cfg' ) : '';
                $version_check = ( $wp_filesystem->exists( $d_path.'version.cfg' ) ) ? $wp_filesystem->get_contents( $d_path.'version.cfg' ) : '';

				if($is_template !== false){
					if($uid_check != $is_template){
						return(array("success"=>false,"error"=>__('Please select the correct zip file, checksum failed!')));
					}
				}else{ //someone imported a template base Slider, check if it is existing in Base Sliders, if yes, check if it was imported
					if($uid_check !== ''){
						$tmpl = new RevSliderTemplate($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
						$tmpl_slider = $tmpl->getThemePunchTemplateSliders();
						
						foreach($tmpl_slider as $tp_slider){
							if(!isset($tp_slider['installed'])) continue;
							
							if($tp_slider['uid'] == $uid_check){
								$is_template = $uid_check;
								break;
							}
						}
					}
				}

				$db = new RevSliderDB($this->_query, $this->_resource);

				//update/insert custom animations
				$animations = @unserialize($animations);
				if(!empty($animations)){
					foreach($animations as $key => $animation){ //$animation['id'], $animation['handle'], $animation['params']
                        $exist = $db->fetch(RevSliderGlobals::$table_layer_anims, $db->prepare("handle = %s", array($animation['handle'])));
						if(!empty($exist)){ //update the animation, get the ID
							if($updateAnim == "true"){ //overwrite animation if exists
								$arrUpdate = array();
								$arrUpdate['params'] = stripslashes(json_encode(str_replace("'", '"', $animation['params'])));
								$db->update(RevSliderGlobals::$table_layer_anims, $arrUpdate, array('handle' => $animation['handle']));

								$anim_id = $exist['0']['id'];
							}else{ //insert with new handle
								$arrInsert = array();
								$arrInsert["handle"] = 'copy_'.$animation['handle'];
								$arrInsert["params"] = stripslashes(json_encode(str_replace("'", '"', $animation['params'])));

								$anim_id = $db->insert(RevSliderGlobals::$table_layer_anims, $arrInsert);
							}
						}else{ //insert the animation, get the ID
							$arrInsert = array();
							$arrInsert["handle"] = $animation['handle'];
							$arrInsert["params"] = stripslashes(json_encode(str_replace("'", '"', $animation['params'])));

							$anim_id = $db->insert(RevSliderGlobals::$table_layer_anims, $arrInsert);
						}
						
						//and set the current customin-oldID and customout-oldID in slider params to new ID from $id
						$content = str_replace(array('customin-'.$animation['id'].'"', 'customout-'.$animation['id'].'"'), array('customin-'.$anim_id.'"', 'customout-'.$anim_id.'"'), $content);	
					}
				}
				
				//overwrite/create dynamic-captions.css
				//parse css to classes
				$dynamicCss = RevSliderCssParser::parseCssToArray($dynamic);
				if(is_array($dynamicCss) && $dynamicCss !== false && count($dynamicCss) > 0){
					foreach($dynamicCss as $class => $styles){
						//check if static style or dynamic style
						$class = trim($class);
						
						if(strpos($class, ',') !== false && strpos($class, '.tp-caption') !== false){ //we have something like .tp-caption.redclass, .redclass
							$class_t = explode(',', $class);
							foreach($class_t as $k => $cl){
								if(strpos($cl, '.tp-caption') !== false) $class = $cl;
							}
						}
						
						if((strpos($class, ':hover') === false && strpos($class, ':') !== false) || //before, after
							strpos($class," ") !== false || // .tp-caption.imageclass img or .tp-caption .imageclass or .tp-caption.imageclass .img
							strpos($class,".tp-caption") === false || // everything that is not tp-caption
							(strpos($class,".") === false || strpos($class,"#") !== false) || // no class -> #ID or img
							strpos($class,">") !== false){ //.tp-caption>.imageclass or .tp-caption.imageclass>img or .tp-caption.imageclass .img
							continue;
						}
						
						//is a dynamic style
						if(strpos($class, ':hover') !== false){
							$class = trim(str_replace(':hover', '', $class));
							$arrInsert = array();
							$arrInsert["hover"] = json_encode($styles);
							$arrInsert["settings"] = json_encode(array('hover' => 'true'));
						}else{
							$arrInsert = array();
							$arrInsert["params"] = json_encode($styles);
							$arrInsert["settings"] = '';
						}
						//check if class exists
                        $result = $db->fetch(RevSliderGlobals::$table_css, $db->prepare("handle = %s", array($class)));
						
						if(!empty($result)){ //update
							$db->update(RevSliderGlobals::$table_css, $arrInsert, array('handle' => $class));
						}else{ //insert
							$arrInsert["handle"] = $class;
							$db->insert(RevSliderGlobals::$table_css, $arrInsert);
						}
					}
				}
				
                //update/insert custom animations
                $navigations = @unserialize($navigations);
                if(!empty($navigations)){
					
                    foreach($navigations as $key => $navigation){
                        $exist = $db->fetch(RevSliderGlobals::$table_navigation, $db->prepare("handle = %s", array($navigation['handle'])));
                        unset($navigation['id']);
                        
                        $rh = $navigation["handle"];
                        if(!empty($exist)){ //create new navigation, get the ID
                            if($updateNavigation == "true"){ //overwrite navigation if exists
                                unset($navigation['handle']);
                                $db->update(RevSliderGlobals::$table_navigation, $navigation, array('handle' => $rh));
                                
                            }else{
                                //insert with new handle
                                $navigation["handle"] = $navigation['handle'].'-'.date('is');
                                $navigation["name"] = $navigation['name'].'-'.date('is');
                                $content = str_replace($rh.'"', $navigation["handle"].'"', $content);
                                $navigation["css"] = str_replace('.'.$rh, '.'.$navigation["handle"], $navigation["css"]); //change css class to the correct new class
                                $navi_id = $db->insert(RevSliderGlobals::$table_navigation, $navigation);
                                
                            }
                        }else{
                            $navi_id = $db->insert(RevSliderGlobals::$table_navigation, $navigation);
                        }
                    }
				}
            }else{
                $message = $unzipfile->get_error_message();

                $wp_filesystem->delete($rem_path, true);
                
                return(array("success"=>false,"error"=>$message));
			}
			
			$content = preg_replace_callback('!s:(\d+):"(.*?)";!', array('self', 'clear_error_in_string') , $content); //clear errors in string
			
			$arrSlider = @unserialize($content);
			if(empty($arrSlider)){
                $wp_filesystem->delete($rem_path, true);
                RevSliderFunctions::throwError(__('Wrong export slider file format! Please make sure that the uploaded file is either a zip file with a correct slider_export.txt in the root of it or an valid slider_export.txt file.'));
			}

			//update slider params
			$sliderParams = $arrSlider["params"];
			
			if($sliderExists){
				$sliderParams["title"] = $this->arrParams["title"];
				$sliderParams["alias"] = $this->arrParams["alias"];
				$sliderParams["shortcode"] = $this->arrParams["shortcode"];
			}
			
			if (isset($sliderParams["background_image"])) {
                $importImage = RevSliderFunctionsWP::import_media($d_path.'images/'.$sliderParams["background_image"], $sliderParams["alias"].'/');
                if ($importImage !== false) {
                    $alreadyImported['images/'.$sliderParams["background_image"]] = $importImage['path'];
                    $sliderParams["background_image"] = $importImage['path'];
                }
                $sliderParams["background_image"] = RevSliderFunctionsWP::getImageUrlFromPath($sliderParams["background_image"]);
			}
			
			$import_statics = true;
			if(isset($sliderParams['enable_static_layers'])){
				if($sliderParams['enable_static_layers'] == 'off') $import_statics = false;
				unset($sliderParams['enable_static_layers']);
			}

            $sliderParams['version'] = $version_check;
            
			$json_params = json_encode($sliderParams);
			
			//update slider or create new
			if($sliderExists){
				$arrUpdate = array("params"=>$json_params);	
				$this->db->update(RevSliderGlobals::$table_sliders,$arrUpdate,array("id"=>$sliderID));
			}else{	//new slider
				$arrInsert = array();
				$arrInsert['params'] = $json_params;
				//check if Slider with title and/or alias exists, if yes change both to stay unique
				
				
				$arrInsert['title'] = RevSliderFunctions::getVal($sliderParams, 'title', 'Slider1');
				$arrInsert['alias'] = RevSliderFunctions::getVal($sliderParams, 'alias', 'slider1');	
				if($is_template === false){ //we want to stay at the given alias if we are a template
					$talias = $arrInsert['alias'];
					$ti = 1;
					while($this->isAliasExistsInDB($this->_framework->sanitize_title_with_dashes($talias))){ //set a new alias and title if its existing in database
						$talias = $arrInsert['alias'].' '.$ti;
						$ti++;
					}

					if($talias !== $arrInsert['alias']){
                        $sliderParams['title'] = $talias;
                        $sliderParams['alias'] = $this->_framework->sanitize_title_with_dashes($talias);
						$arrInsert['title'] = $talias;
						$arrInsert['alias'] = $this->_framework->sanitize_title_with_dashes($talias);
                        $json_params = json_encode($sliderParams);
                        $arrInsert['params'] = $json_params;
					}
				}
				
				if($is_template !== false){ //add that we are an template
					$arrInsert['type'] = 'template';
                    $sliderParams['uid'] = $is_template;
                    $json_params = json_encode($sliderParams);
                    $arrInsert['params'] = $json_params;
				}



				$sliderID = $this->db->insert(RevSliderGlobals::$table_sliders,$arrInsert);
			}
			
			//-------- Slides Handle -----------
			
			//delete current slides
			if($sliderExists)
				$this->deleteAllSlides();
			
			//create all slides
			$arrSlides = $arrSlider["slides"];
			
			$alreadyImported = array();
			
            $content_url = $this->_framework->getAssetUrl('', array('_area' => 'frontend')) . '/';
            
			//wpml compatibility
			$slider_map = array();
			foreach($arrSlides as $sl_key => $slide){
				$params = $slide["params"];
				$layers = $slide["layers"];
				$settings = (isset($slide["settings"])) ? $slide["settings"] : '';
				
				//convert params images:
				if($importZip === true){ //we have a zip, check if exists
                    //remove image_id as it is not needed in import
                    if(isset($params['image_id'])) unset($params['image_id']);

					if(isset($params["image"]) && ! empty($params["image"])){
						$params["image"] = RevSliderBase::check_file_in_zip($d_path, $params["image"], $sliderParams["alias"], $alreadyImported);
						$params["image"] = RevSliderFunctionsWP::getImageUrlFromPath($params["image"]);
					}

					if(isset($params["background_image"]) && ! empty($params["background_image"])){
						$params["background_image"] = RevSliderBase::check_file_in_zip($d_path, $params["background_image"], $sliderParams["alias"], $alreadyImported);
						$params["background_image"] = RevSliderFunctionsWP::getImageUrlFromPath($params["background_image"]);
					}

					if(isset($params["slide_thumb"]) && ! empty($params["slide_thumb"])) {
						$params["slide_thumb"] = RevSliderBase::check_file_in_zip($d_path, $params["slide_thumb"], $sliderParams["alias"], $alreadyImported);
						$params["slide_thumb"] = RevSliderFunctionsWP::getImageUrlFromPath($params["slide_thumb"]);
					}

					if(isset($params["show_alternate_image"]) && ! empty($params["show_alternate_image"])) {
						$params["show_alternate_image"] = RevSliderBase::check_file_in_zip($d_path, $params["show_alternate_image"], $sliderParams["alias"], $alreadyImported);
						$params["show_alternate_image"] = RevSliderFunctionsWP::getImageUrlFromPath($params["show_alternate_image"]);
					}
					if(isset($params['background_type']) && $params['background_type'] == 'html5'){
						if(isset($params['slide_bg_html_mpeg']) && $params['slide_bg_html_mpeg'] != ''){
							$params['slide_bg_html_mpeg'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $params["slide_bg_html_mpeg"], $sliderParams["alias"], $alreadyImported, true));
						}
						if(isset($params['slide_bg_html_webm']) && $params['slide_bg_html_webm'] != ''){
							$params['slide_bg_html_webm'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $params["slide_bg_html_webm"], $sliderParams["alias"], $alreadyImported, true));
						}
						if(isset($params['slide_bg_html_ogv'])  && $params['slide_bg_html_ogv'] != ''){
							$params['slide_bg_html_ogv'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $params["slide_bg_html_ogv"], $sliderParams["alias"], $alreadyImported, true));
						}
					}
				}


				//convert layers images:
				foreach($layers as $key=>$layer){
					//import if exists in zip folder
					if($importZip === true){ //we have a zip, check if exists
						if(isset($layer["image_url"])){
							$layer["image_url"] = RevSliderBase::check_file_in_zip($d_path, $layer["image_url"], $sliderParams["alias"], $alreadyImported);
							$layer["image_url"] = RevSliderFunctionsWP::getImageUrlFromPath($layer["image_url"]);
						}
                        if(isset($layer["bgimage_url"])){
                            $layer["bgimage_url"] = RevSliderBase::check_file_in_zip($d_path, $layer["bgimage_url"], $sliderParams["alias"], $alreadyImported);
                            $layer["bgimage_url"] = RevSliderFunctionsWP::getImageUrlFromPath($layer["bgimage_url"]);
                        }
                        if(isset($layer['type']) && ($layer['type'] == 'video' || $layer['type'] == 'audio')){

							$video_data = (isset($layer['video_data'])) ? (array) $layer['video_data'] : array();

							if(!empty($video_data) && isset($video_data['video_type']) && $video_data['video_type'] == 'html5'){

								if(isset($video_data['urlPoster']) && $video_data['urlPoster'] != ''){
									$video_data['urlPoster'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $video_data["urlPoster"], $sliderParams["alias"], $alreadyImported));
								}

								if(isset($video_data['urlMp4']) && $video_data['urlMp4'] != ''){
									$video_data['urlMp4'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $video_data["urlMp4"], $sliderParams["alias"], $alreadyImported, true));
								}
								if(isset($video_data['urlWebm']) && $video_data['urlWebm'] != ''){
									$video_data['urlWebm'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $video_data["urlWebm"], $sliderParams["alias"], $alreadyImported, true));
								}
								if(isset($video_data['urlOgv']) && $video_data['urlOgv'] != ''){
									$video_data['urlOgv'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $video_data["urlOgv"], $sliderParams["alias"], $alreadyImported, true));
								}

							}elseif(!empty($video_data) && isset($video_data['video_type']) && $video_data['video_type'] != 'html5'){ //video cover image
                                if($video_data['video_type'] == 'audio'){
                                    if(isset($video_data['urlAudio']) && $video_data['urlAudio'] != ''){
                                        $video_data['urlAudio'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $video_data["urlAudio"], $sliderParams["alias"], $alreadyImported, true));
                                    }
                                }else{
								if(isset($video_data['previewimage']) && $video_data['previewimage'] != ''){
									$video_data['previewimage'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $video_data["previewimage"], $sliderParams["alias"], $alreadyImported));
								}
							}
                            }
							
							$layer['video_data'] = $video_data;
							
							if(isset($layer['video_image_url']) && $layer['video_image_url'] != ''){
								$layer['video_image_url'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $layer["video_image_url"], $sliderParams["alias"], $alreadyImported));
							}
						}
						
                        if(isset($layer['type']) && $layer['type'] == 'svg'){
                            if(isset($layer['svg']) && isset($layer['svg']->src)){
                                if (strpos($layer['svg']->src, '/plugins/revslider/') === 0) {
                                    $layer['svg']->src = $content_url . substr($layer['svg']->src, strlen('/plugins/revslider/'));
                                } elseif (strpos($layer['svg']->src, '/plugins/') === 0) {
                                    $layer['svg']->src = $this->_images->imageBaseUrl() . substr($layer['svg']->src, 1);
                                } elseif (strpos($layer['svg']->src, 'revslider/public/') === 0) {
                                    $layer['svg']->src = $content_url . substr($layer['svg']->src, strlen('revslider/'));
                                } else {
                                    $layer['svg']->src = $content_url . $layer['svg']->src;
                                }
                            }
                        }

					}

					$layer['text'] = stripslashes($layer['text']);
					$layers[$key] = $layer;
				}

				$arrSlides[$sl_key]['layers'] = $layers;
				
				//create new slide
				$arrCreate = array();
				$arrCreate["slider_id"] = $sliderID;
				$arrCreate["slide_order"] = $slide["slide_order"];

                $d = array('params' => $params, 'sliderParams' => $sliderParams, 'layers' => $layers, 'settings' => $settings, 'alreadyImported' => $alreadyImported);
                $d = $this->_framework->apply_filters('revslider_importSliderFromPost_modify_data', $d, 'normal', $d_path);

                $params = $d['params'];
                $sliderParams = $d['sliderParams'];
                $layers = $d['layers'];
                $settings = $d['settings'];
                $alreadyImported = $d['alreadyImported'];

				$my_layers = json_encode($layers);
				if(empty($my_layers))
					$my_layers = stripslashes(json_encode($layers));
				$my_params = json_encode($params);
				if(empty($my_params))
					$my_params = stripslashes(json_encode($params));
				$my_settings = json_encode($settings);
				if(empty($my_settings))
					$my_settings = stripslashes(json_encode($settings));
				
				
					
				$arrCreate["layers"] = $my_layers;
				$arrCreate["params"] = $my_params;
				$arrCreate["settings"] = $my_settings;
				
				$last_id = $this->db->insert(RevSliderGlobals::$table_slides,$arrCreate);
				
				if(isset($slide['id'])){
					$slider_map[$slide['id']] = $last_id;
				}
			}
			
			//change for WPML the parent IDs if necessary
			if(!empty($slider_map)){
				foreach($arrSlides as $sl_key => $slide){
					if(isset($slide['params']['parentid']) && isset($slider_map[$slide['params']['parentid']])){
						$update_id = $slider_map[$slide['id']];
						$parent_id = $slider_map[$slide['params']['parentid']];
						
						$arrCreate = array();
						
						$arrCreate["params"] = $slide['params'];
						$arrCreate["params"]['parentid'] = $parent_id;
						$my_params = json_encode($arrCreate["params"]);
						if(empty($my_params))
							$my_params = stripslashes(json_encode($arrCreate["params"]));
						
						$arrCreate["params"] = $my_params;
						
						$this->db->update(RevSliderGlobals::$table_slides,$arrCreate,array("id"=>$update_id));
					}
					
					$did_change = false;
					foreach($slide['layers'] as $key => $value){
						if(isset($value['layer_action'])){
							if(isset($value['layer_action']->jump_to_slide) && !empty($value['layer_action']->jump_to_slide)){
								$value['layer_action']->jump_to_slide = (array)$value['layer_action']->jump_to_slide;
								foreach($value['layer_action']->jump_to_slide as $jtsk => $jtsval){
									if(isset($slider_map[$jtsval])){
										$slide['layers'][$key]['layer_action']->jump_to_slide[$jtsk] = $slider_map[$jtsval];
										$did_change = true;
									}
								}
							}
						}
						
						$link_slide = RevSliderFunctions::getVal($value, 'link_slide', false);
						if($link_slide != false && $link_slide !== 'nothing'){ //link to slide/scrollunder is set, move it to actions
							if(!isset($slide['layers'][$key]['layer_action'])) $slide['layers'][$key]['layer_action'] = new \stdClass();
							switch($link_slide){
								case 'link':
									$link = RevSliderFunctions::getVal($value, 'link');
									$link_open_in = RevSliderFunctions::getVal($value, 'link_open_in');
									$slide['layers'][$key]['layer_action']->action = array('a' => 'link');
									$slide['layers'][$key]['layer_action']->link_type = array('a' => 'a');
									$slide['layers'][$key]['layer_action']->image_link = array('a' => $link);
									$slide['layers'][$key]['layer_action']->link_open_in = array('a' => $link_open_in);
									
									unset($slide['layers'][$key]['link']);
									unset($slide['layers'][$key]['link_open_in']);
								case 'next':
									$slide['layers'][$key]['layer_action']->action = array('a' => 'next');
								break;
								case 'prev':
									$slide['layers'][$key]['layer_action']->action = array('a' => 'prev');
								break;
								case 'scroll_under':
									$scrollunder_offset = RevSliderFunctions::getVal($value, 'scrollunder_offset');
									$slide['layers'][$key]['layer_action']->action = array('a' => 'scroll_under');
									$slide['layers'][$key]['layer_action']->scrollunder_offset = array('a' => $scrollunder_offset);
									
									unset($slide['layers'][$key]['scrollunder_offset']);
								break;
								default: //its an ID, so its a slide ID
									$slide['layers'][$key]['layer_action']->action = array('a' => 'jumpto');
									$slide['layers'][$key]['layer_action']->jump_to_slide = array('a' => $slider_map[$link_slide]);
								break;
								
							}
							$slide['layers'][$key]['layer_action']->tooltip_event = array('a' => 'click');
							
							unset($slide['layers'][$key]['link_slide']);
							
							$did_change = true;
						}
						
						
						if($did_change === true){
							
							$arrCreate = array();
							$my_layers = json_encode($slide['layers']);
							if(empty($my_layers))
								$my_layers = stripslashes(json_encode($layers));
							
							$arrCreate['layers'] = $my_layers;
							
							$this->db->update(RevSliderGlobals::$table_slides,$arrCreate,array("id"=>$slider_map[$slide['id']]));
						}
					}
				}
			}
			
			//check if static slide exists and import
			if(isset($arrSlider['static_slides']) && !empty($arrSlider['static_slides']) && $import_statics){
				$static_slide = $arrSlider['static_slides'];
				foreach($static_slide as $slide){
					
					$params = $slide["params"];
					$layers = $slide["layers"];
					$settings = (isset($slide["settings"])) ? $slide["settings"] : '';
					
                    //remove image_id as it is not needed in import
                    if(isset($params['image_id'])) unset($params['image_id']);
					
					//convert params images:
					if(isset($params["image"])){
						//import if exists in zip folder
						if(strpos($params["image"], 'http') !== false){
						}else{
							if(trim($params["image"]) !== ''){
								if($importZip === true){ //we have a zip, check if exists
									$image = $wp_filesystem->exists( $d_path.'images/'.$params["image"] );
									if(!$image){
										echo $params["image"].__(' not found!<br>');
									}else{
										if(!isset($alreadyImported['images/'.$params["image"]])){
											$importImage = RevSliderFunctionsWP::import_media($d_path.'images/'.$params["image"], $sliderParams["alias"].'/');

											if($importImage !== false){
												$alreadyImported['images/'.$params["image"]] = $importImage['path'];

												$params["image"] = $importImage['path'];
											}
										}else{
											$params["image"] = $alreadyImported['images/'.$params["image"]];
										}


									}
								}
							}
							$params["image"] = RevSliderFunctionsWP::getImageUrlFromPath($params["image"]);
						}
					}
					
					//convert layers images:
					foreach($layers as $key=>$layer){
						if(isset($layer["image_url"])){
							//import if exists in zip folder
							if(trim($layer["image_url"]) !== ''){
								if(strpos($layer["image_url"], 'http') !== false){
								}else{
									if($importZip === true){ //we have a zip, check if exists
										$image_url = $wp_filesystem->exists( $d_path.'images/'.$layer["image_url"] );
										if(!$image_url){
											echo $layer["image_url"].__(' not found!<br>');
										}else{
											if(!isset($alreadyImported['images/'.$layer["image_url"]])){
												$importImage = RevSliderFunctionsWP::import_media($d_path.'images/'.$layer["image_url"], $sliderParams["alias"].'/');

												if($importImage !== false){
													$alreadyImported['images/'.$layer["image_url"]] = $importImage['path'];

													$layer["image_url"] = $importImage['path'];
												}
											}else{
												$layer["image_url"] = $alreadyImported['images/'.$layer["image_url"]];
											}
										}
									}
								}
							}
							$layer["image_url"] = RevSliderFunctionsWP::getImageUrlFromPath($layer["image_url"]);
                        }
                        if(isset($layer["bgimage_url"])){
                            //import if exists in zip folder
                            if(trim($layer["bgimage_url"]) !== ''){
                                if(strpos($layer["bgimage_url"], 'http') !== false){
                                }else{
                                    if($importZip === true){ //we have a zip, check if exists
                                        $bgimage_url = $wp_filesystem->exists( $d_path.'images/'.$layer["bgimage_url"] );
                                        if(!$bgimage_url){
                                            echo $layer["bgimage_url"].__(' not found!<br>');
                                        }else{
                                            if(!isset($alreadyImported['images/'.$layer["bgimage_url"]])){
                                                $importImage = RevSliderFunctionsWP::import_media($d_path.'images/'.$layer["bgimage_url"], $sliderParams["alias"].'/');

                                                if($importImage !== false){
                                                    $alreadyImported['images/'.$layer["bgimage_url"]] = $importImage['path'];

                                                    $layer["bgimage_url"] = $importImage['path'];
                                                }
                                            }else{
                                                $layer["bgimage_url"] = $alreadyImported['images/'.$layer["bgimage_url"]];
                                            }
                                        }
                                    }
                                }
                            }
                            $layer["bgimage_url"] = RevSliderFunctionsWP::getImageUrlFromPath($layer["bgimage_url"]);
                        }

                        $layer['text'] = stripslashes($layer['text']);
							
                        if(isset($layer['type']) && ($layer['type'] == 'video' || $layer['type'] == 'audio')){
                            
                            $video_data = (isset($layer['video_data'])) ? (array) $layer['video_data'] : array();
                            
                            if(!empty($video_data) && isset($video_data['video_type']) && $video_data['video_type'] == 'html5'){

                                if(isset($video_data['urlPoster']) && $video_data['urlPoster'] != ''){
                                    $video_data['urlPoster'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $video_data["urlPoster"], $sliderParams["alias"], $alreadyImported));
                                }
                                if(isset($video_data['urlMp4']) && $video_data['urlMp4'] != ''){
                                    $video_data['urlMp4'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $video_data["urlMp4"], $sliderParams["alias"], $alreadyImported, true));
                                }
                                if(isset($video_data['urlWebm']) && $video_data['urlWebm'] != ''){
                                    $video_data['urlWebm'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $video_data["urlWebm"], $sliderParams["alias"], $alreadyImported, true));
                                }
                                if(isset($video_data['urlOgv']) && $video_data['urlOgv'] != ''){
                                    $video_data['urlOgv'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $video_data["urlOgv"], $sliderParams["alias"], $alreadyImported, true));
                                }
                                
                            }elseif(!empty($video_data) && isset($video_data['video_type']) && $video_data['video_type'] != 'html5'){ //video cover image
                                if($video_data['video_type'] == 'audio'){
                                    if(isset($video_data['urlAudio']) && $video_data['urlAudio'] != ''){
                                        $video_data['urlAudio'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $video_data["urlAudio"], $sliderParams["alias"], $alreadyImported, true));
                                    }
                                }else{
                                    if(isset($video_data['previewimage']) && $video_data['previewimage'] != ''){
                                        $video_data['previewimage'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $video_data["previewimage"], $sliderParams["alias"], $alreadyImported));
                                    }
                                }
                            }
                            
                            $layer['video_data'] = $video_data;
							
							if(isset($layer['video_image_url']) && $layer['video_image_url'] != ''){
								$layer['video_image_url'] = RevSliderFunctionsWP::getImageUrlFromPath(RevSliderBase::check_file_in_zip($d_path, $layer["video_image_url"], $sliderParams["alias"], $alreadyImported));
							}
                        }
                        
                        if(isset($layer['type']) && $layer['type'] == 'svg'){
                            if(isset($layer['svg']) && isset($layer['svg']->src)){
                                if (strpos($layer['svg']->src, '/plugins/revslider/') === 0) {
                                    $layer['svg']->src = $content_url . substr($layer['svg']->src, strlen('/plugins/revslider/'));
                                } elseif (strpos($layer['svg']->src, '/plugins/') === 0) {
                                    $layer['svg']->src = $this->_images->imageBaseUrl() . substr($layer['svg']->src, 1);
                                } elseif (strpos($layer['svg']->src, 'revslider/public/') === 0) {
                                    $layer['svg']->src = $content_url . substr($layer['svg']->src, strlen('revslider/'));
                                } else {
                                    $layer['svg']->src = $content_url . $layer['svg']->src;
                                }
                            }
						}

						if(isset($layer['layer_action'])){
							if(isset($layer['layer_action']->jump_to_slide) && !empty($layer['layer_action']->jump_to_slide)){
								foreach($layer['layer_action']->jump_to_slide as $jtsk => $jtsval){
									if(isset($slider_map[$jtsval])){
										$layer['layer_action']->jump_to_slide[$jtsk] = $slider_map[$jtsval];
									}
								}
							}
						}
						
                        $link_slide = RevSliderFunctions::getVal($layer, 'link_slide', false);
						if($link_slide != false && $link_slide !== 'nothing'){ //link to slide/scrollunder is set, move it to actions
							if(!isset($layer['layer_action'])) $layer['layer_action'] = new \stdClass();

							switch($link_slide){
								case 'link':
                                    $link = RevSliderFunctions::getVal($layer, 'link');
                                    $link_open_in = RevSliderFunctions::getVal($layer, 'link_open_in');
									$layer['layer_action']->action = array('a' => 'link');
									$layer['layer_action']->link_type = array('a' => 'a');
									$layer['layer_action']->image_link = array('a' => $link);
									$layer['layer_action']->link_open_in = array('a' => $link_open_in);

									unset($layer['link']);
									unset($layer['link_open_in']);
								case 'next':
									$layer['layer_action']->action = array('a' => 'next');
								break;
								case 'prev':
									$layer['layer_action']->action = array('a' => 'prev');
								break;
								case 'scroll_under':
									$scrollunder_offset = RevSliderFunctions::getVal($value, 'scrollunder_offset');
									$layer['layer_action']->action = array('a' => 'scroll_under');
									$layer['layer_action']->scrollunder_offset = array('a' => $scrollunder_offset);

									unset($layer['scrollunder_offset']);
								break;
								default: //its an ID, so its a slide ID
									$layer['layer_action']->action = array('a' => 'jumpto');
									$layer['layer_action']->jump_to_slide = array('a' => $slider_map[$link_slide]);
								break;

							}
							$layer['layer_action']->tooltip_event = array('a' => 'click');
							
							unset($layer['link_slide']);
							
							$did_change = true;
						}
						
						$layers[$key] = $layer;
					}

                    $d = array('params' => $params, 'layers' => $layers, 'settings' => $settings);
                    $d = $this->_framework->apply_filters('revslider_importSliderFromPost_modify_data', $d, 'static', $d_path);

                    $params = $d['params'];
                    $layers = $d['layers'];
                    $settings = $d['settings'];

					//create new slide
					$arrCreate = array();
					$arrCreate["slider_id"] = $sliderID;
					
					$my_layers = json_encode($layers);
					if(empty($my_layers))
						$my_layers = stripslashes(json_encode($layers));
					$my_params = json_encode($params);
					if(empty($my_params))
						$my_params = stripslashes(json_encode($params));
					$my_settings = json_encode($settings);
					if(empty($my_settings))
						$my_settings = stripslashes(json_encode($settings));
						
						
					$arrCreate["layers"] = $my_layers;
					$arrCreate["params"] = $my_params;
					$arrCreate["settings"] = $my_settings;
					
					if($sliderExists){
						unset($arrCreate["slider_id"]);
						$this->db->update(RevSliderGlobals::$table_static_slides,$arrCreate,array("slider_id"=>$sliderID));
					}else{
						$this->db->insert(RevSliderGlobals::$table_static_slides,$arrCreate);
					}
				}
			}
			
			$c_slider = new RevSliderSlider($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
			$c_slider->initByID($sliderID);
			
			//check to convert styles to latest versions
			RevSliderPluginUpdate::update_css_styles(); //set to version 5
			RevSliderPluginUpdate::add_animation_settings_to_layer($c_slider); //set to version 5
			RevSliderPluginUpdate::add_style_settings_to_layer($c_slider); //set to version 5
			RevSliderPluginUpdate::change_settings_on_layers($c_slider); //set to version 5
			RevSliderPluginUpdate::add_general_settings($c_slider); //set to version 5
			RevSliderPluginUpdate::change_general_settings_5_0_7($c_slider); //set to version 5.0.7
            RevSliderPluginUpdate::change_layers_svg_5_2_5_5($c_slider); //set to version 5.2.5.5
			
			$cus_js = $c_slider->getParam('custom_javascript', '');
			
			if(strpos($cus_js, 'revapi') !== false){
				if(preg_match_all('/revapi[0-9]*/', $cus_js, $results)){
					
					if(isset($results[0]) && !empty($results[0])){
						foreach($results[0] as $replace){
							$cus_js = str_replace($replace, 'revapi'.$sliderID, $cus_js);
						}
					}
					
					$c_slider->updateParam(array('custom_javascript' => $cus_js));
					
				}
				
			}

            $real_slider_id = $sliderID;

            if($is_template !== false){ //duplicate the slider now, as we just imported the "template"
				if($single_slide !== false){ //add now one Slide to the current Slider
					$mslider = new RevSliderSlider($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
					
					//change slide_id to correct, as it currently is just a number beginning from 0 as we did not have a correct slide ID yet.
					$i = 0;
					$changed = false;
					foreach($slider_map as $value){
						if($i == $single_slide['slide_id']){
							$single_slide['slide_id'] = $value;
							$changed = true;
							break;
						}
						$i++;
					}
					
					if($changed){
						$return = $mslider->copySlideToSlider($single_slide);
					}else{
						return(array("success"=>false,"error"=>__('could not find correct Slide to copy, please try again.'),"sliderID"=>$sliderID));
					}
					
				}else{
					$mslider = new RevSliderSlider($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
					$title = $this->_framework->sanitize_title( RevSliderFunctions::getVal($sliderParams, 'title', 'slider1') );
					$talias = $title;
					$ti = 1;
					while($this->isAliasExistsInDB( $this->_framework->sanitize_title_with_dashes($talias) )) { //set a new alias and title if its existing in database
						$talias = $title.' '.$ti;
						$ti++;
					}
                    $real_slider_id = $mslider->duplicateSliderFromData(array('sliderid' => $sliderID, 'title' => $talias));
				}
			}

            $wp_filesystem->delete($rem_path, true);
			

		}catch(\Exception $e){
            Data::logException($e);
			$errorMessage = $e->getMessage();

            if(isset($rem_path)){
                $wp_filesystem->delete($rem_path, true);
			}
			return(array("success"=>false,"error"=>$errorMessage,"sliderID"=>$sliderID));
		}

        $this->_framework->do_action('revslider_slider_imported', $real_slider_id);

        return(array("success"=>true,"sliderID"=>$real_slider_id));
	}
	
	
	/**
	 * 
	 * update slider from options
	 */
	public function updateSliderFromOptions($options){
		
		$sliderID = RevSliderFunctions::getVal($options, "sliderid");
		RevSliderFunctions::validateNotEmpty($sliderID,"Slider ID");
		
		$this->createUpdateSliderFromOptions($options,$sliderID);
	}
	
	/**
	 * 
	 * update some params in the slider
	 */
	public function updateParam($arrUpdate){
		$this->validateInited();
		
		$this->arrParams = array_merge($this->arrParams,$arrUpdate);
		$jsonParams = json_encode($this->arrParams);
		$arrUpdateDB = array();
		$arrUpdateDB["params"] = $jsonParams;
		
		$this->db->update(RevSliderGlobals::$table_sliders,$arrUpdateDB,array("id"=>$this->id));
	}
	
	/**
	 * update some settings in the slider
	 */
	public function updateSetting($arrUpdate){
		$this->validateInited();
		
		$this->settings = array_merge($this->settings,$arrUpdate);
		$jsonParams = json_encode($this->settings);
		$arrUpdateDB = array();
		$arrUpdateDB["settings"] = $jsonParams;
		
		$this->db->update(RevSliderGlobals::$table_sliders,$arrUpdateDB,array("id"=>$this->id));
	}
	
	
	/**
	 * 
	 * delete slider from input data
	 */
	public function deleteSliderFromData($data){
		$sliderID = RevSliderFunctions::getVal($data, "sliderid");
		RevSliderFunctions::validateNotEmpty($sliderID,"Slider ID");
		$this->initByID($sliderID);
		
		$this->deleteSlider();
		
		return true;
	}


    /**
     *
     * duplicate slider from input data
     */
    public function duplicateSliderFromData($data){
        $sliderID = RevSliderFunctions::getVal($data, "sliderid");
        RevSliderFunctions::validateNotEmpty($sliderID,"Slider ID");
        $this->initByID($sliderID);
        $slider_id = $this->duplicateSlider(RevSliderFunctions::getVal($data, "title"));
        return $slider_id;
    }


    /**
     * duplicate slider from input data
     * @since: 5.2.5
     */
    public function duplicateSliderPackageFromData($data){
        $tmpl = new RevSliderTemplate($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);

        $slider_uid = RevSliderFunctions::getVal($data, "slideruid");

        $uids = $tmpl->get_package_uids($slider_uid);

        foreach($uids as $sid => $uid){
            if($sid < 0){ //one or more still needs to be downloaded...
                return __('Please install Package first to use this feature');
            }
        }

        foreach($uids as $sliderID => $uid){
            $slider = new RevSliderSlider($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
            $slider->initByID($sliderID);
            $slider_id = $slider->duplicateSlider(RevSliderFunctions::getVal($data, "title"), true);
        }

        return true;
    }

	
	/**
	 * 
	 * duplicate slide from input data
	 */
	public function duplicateSlideFromData($data){
		
		//init the slider
		$sliderID = RevSliderFunctions::getVal($data, "sliderID");
		RevSliderFunctions::validateNotEmpty($sliderID,"Slider ID");
		$this->initByID($sliderID);
		
		//get the slide id
		$slideID = RevSliderFunctions::getVal($data, "slideID");
		RevSliderFunctions::validateNotEmpty($slideID,"Slide ID");
		$newSlideID = $this->duplicateSlide($slideID);
		
		$this->duplicateChildren($slideID, $newSlideID);
		
		return(array($sliderID, $newSlideID));
	}
	
	
	/**
	 * duplicate slide children
	 * @param $slideID
	 */
	private function duplicateChildren($slideID,$newSlideID){
		
		$arrChildren = $this->getArrSlideChildren($slideID);
		
		foreach($arrChildren as $childSlide){
			$childSlideID = $childSlide->getID();
			//duplicate
			$duplicatedSlideID = $this->duplicateSlide($childSlideID);
			
			//update parent id
			$duplicatedSlide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
			$duplicatedSlide->initByID($duplicatedSlideID);
			$duplicatedSlide->updateParentSlideID($newSlideID);
		}
		
	}
	

	/**
	 * copy slide from one Slider to the given Slider ID
	 * @since: 5.0
	 */
	public function copySlideToSlider($data){
		$wpdb = $this->_query;
		
		$sliderID = intval(RevSliderFunctions::getVal($data, "slider_id"));
		RevSliderFunctions::validateNotEmpty($sliderID,"Slider ID");
		$slideID = intval(RevSliderFunctions::getVal($data, "slide_id"));
		RevSliderFunctions::validateNotEmpty($slideID,"Slide ID");
		
		$tableSliders = $wpdb->prefix . RevSliderGlobals::TABLE_SLIDERS_NAME;
		$tableSlides = $wpdb->prefix . RevSliderGlobals::TABLE_SLIDES_NAME;

		//check if ID exists
		$add_to_slider = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tableSliders WHERE id = %s", $sliderID), Query::ARRAY_A);
		
		if(empty($add_to_slider))
			return __('Slide could not be duplicated');
		
		//get last slide in slider for the order
		$slide_order = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tableSlides WHERE slider_id = %s ORDER BY slide_order DESC", $sliderID), Query::ARRAY_A);
		$order = (empty($slide_order)) ? 1 : $slide_order['slide_order'] + 1;
		
		$slide_to_copy = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tableSlides WHERE id = %s", $slideID), Query::ARRAY_A);
		
		if(empty($slide_to_copy))
			return __('Slide could not be duplicated');
		
		unset($slide_to_copy['id']); //remove the ID of Slide, as it will be a new Slide
		$slide_to_copy['slider_id'] = $sliderID; //set the new Slider ID to the Slide
		$slide_to_copy['slide_order'] = $order; //set the next slide order, to set slide to the end
		
		$response = $wpdb->insert($tableSlides, $slide_to_copy);
		
		if($response === false) return __('Slide could not be copied');
		
		return true;
	}
	
	
	/**
	 * copy / move slide from data
	 */
	public function copyMoveSlideFromData($data){
		
		$sliderID = RevSliderFunctions::getVal($data, "sliderID");
		RevSliderFunctions::validateNotEmpty($sliderID,"Slider ID");
		$this->initByID($sliderID);

		$targetSliderID = RevSliderFunctions::getVal($data, "targetSliderID");
		RevSliderFunctions::validateNotEmpty($sliderID,"Target Slider ID");
		$this->initByID($sliderID);
		
		if($targetSliderID == $sliderID)
			RevSliderFunctions::throwError("The target slider can't be equal to the source slider");
		
		$slideID = RevSliderFunctions::getVal($data, "slideID");
		RevSliderFunctions::validateNotEmpty($slideID,"Slide ID");
		
		$operation = RevSliderFunctions::getVal($data, "operation");
		
		$this->copyMoveSlide($slideID,$targetSliderID,$operation);
		
		return($sliderID);
	}
	
	
	/**
	 * create a slide from input data
	 */
	public function createSlideFromData($data,$returnSlideID = false){
		
		$sliderID = RevSliderFunctions::getVal($data, "sliderid");
		$obj = RevSliderFunctions::getVal($data, "obj");
		
		RevSliderFunctions::validateNotEmpty($sliderID,"Slider ID");
		$this->initByID($sliderID);
		
		if(is_array($obj)){	//multiple
			foreach($obj as $item){
				$slide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
				$slideID = $slide->createSlide($sliderID, $item);
			}
			
			return(count($obj));
			
		}else{	//signle
			$urlImage = $obj;
			$slide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
			$slideID = $slide->createSlide($sliderID, $urlImage);
			if($returnSlideID == true)
				return($slideID);
			else 
				return(1);	//num slides -1 slide created
		}
	}
	
	
	
	/**
	 * update slides order from data
	 */
	public function updateSlidesOrderFromData($data){
		$sliderID = RevSliderFunctions::getVal($data, "sliderID");
		$arrIDs = RevSliderFunctions::getVal($data, "arrIDs");
		RevSliderFunctions::validateNotEmpty($arrIDs,"slides");
		
		$this->initByID($sliderID);
		
		$isFromPosts = $this->isSlidesFromPosts();
		
		foreach($arrIDs as $index=>$slideID){
			
			$order = $index+1;
			
			if($isFromPosts){
				RevSliderFunctionsWP::updatePostOrder($slideID, $order);
			}else{
				
				$arrUpdate = array("slide_order"=>$order);
				$where = array("id"=>$slideID);
				$this->db->update(RevSliderGlobals::$table_slides,$arrUpdate,$where);
			}							
		}//end foreach
		
		//update sortby			
		if($isFromPosts){
			$arrUpdate = array();
			$arrUpdate["post_sortby"] = RevSliderFunctionsWP::SORTBY_MENU_ORDER;
			$this->updateParam($arrUpdate);
		} 
		
	}
	
	/**
	 * 
	 * get the "main" and "settings" arrays, for dealing with the settings.
	 */
	public function getSettingsFields(){
		$this->validateInited();
		
		$arrMain = array();
		$arrMain["title"] = $this->title;
		$arrMain["alias"] = $this->alias;
		
		$arrRespose = array("main"=>$arrMain, "params"=>$this->arrParams);
		
		return($arrRespose);
	}
	
	
	/**
	 * get all used fonts in the current Slider
	 * @since: 5.1.0
	 */
	public function getUsedFonts($full = false){
		$this->validateInited();
		$gf = array();
		
        $sl = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
        
		$mslides = $this->getSlides(true);
        
        $staticID = $sl->getStaticSlideID($this->getID());
        if($staticID !== false){
            $msl = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
            if(strpos($staticID, 'static_') === false){
                $staticID = 'static_'.$this->getID();
            }
            $msl->initByID($staticID);
            if($msl->getID() !== ''){
                $mslides = array_merge($mslides, array($msl));
            }
        }
        
		if(!empty($mslides)){
			foreach($mslides as $ms){
				$mf = $ms->getUsedFonts($full);
				if(!empty($mf)){
					foreach($mf as $mfk => $mfv){
						if(!isset($gf[$mfk])){
							$gf[$mfk] = $mfv;
						}else{
							foreach($mfv['variants'] as $mfvk => $mfvv){
								$gf[$mfk]['variants'][$mfvk] = true;
							}
						}
						$gf[$mfk]['slide'][] = array('id' => $ms->getID(), 'title' => $ms->getTitle());
					}
				}
			}
		}

        return $this->_framework->apply_filters('revslider_getUsedFonts', $gf);
	}
	
	
	/**
	 * get slides from gallery
	 * force from gallery - get the slide from the gallery only
	 */
	public function getSlides($publishedOnly = false, $storeID = 0){

		$arrSlides = $this->getSlidesFromGallery($publishedOnly, $storeID);
		
		return($arrSlides);
	}


	/**
     * get slides from gallery
     * get the slides raw, do not initialize them
     * @since: 5.3.0
     */
    public function getSlidesCountRaw($publishedOnly = false){

        $arrSlides = $this->getSlidesCountFromGallery($publishedOnly);

        return($arrSlides);
    }


    /**
     * get first slide from gallery
     * @since: 5.3.0
     */
    public function getFirstSlide($publishedOnly = false){

        $arrSlides = $this->getSlidesFromGallery($publishedOnly, 0, false, true);

        return($arrSlides);
    }


    /**
	 * get slides from posts
	 */
    public function getSlidesFromPosts($publishedOnly = false, $gal_ids = array()){
		
		$slideTemplates = $this->getSlidesFromGallery($publishedOnly);
		$slideTemplates = RevSliderFunctions::assocToArray($slideTemplates);
		
		if(count($slideTemplates) == 0) return array();
		
		$sourceType = $this->getParam("source_type","gallery");
        
        if(!empty($gal_ids)) $sourceType = 'specific_posts'; //change to specific posts, give the gal_ids to the list
		switch($sourceType){
			case "posts":
                //check where to get posts from
                $sourceType = $this->getParam("fetch_type","cat_tag");
                switch($sourceType){
                    case 'cat_tag':
                    default:
                        $arrPosts = $this->getPostsFromCategories($publishedOnly);
                    break;
                    case 'related':
                        $arrPosts = $this->getPostsFromRelated();
                    break;
                    case 'popular':
                        $arrPosts = $this->getPostsFromPopular();
                    break;
                    case 'recent':
                        $arrPosts = $this->getPostsFromRecent();
                    break;
                    case 'next_prev':
                        $arrPosts = $this->getPostsNextPrevious();
                    break;
                }
			break;
            case "current_post":
                $currentProductId = $this->_framework->getCurrentProductId();
                $arrPosts = $this->getPostsFromSpecificList(array('', $currentProductId));
                break;
			case "specific_posts":
                $arrPosts = $this->getPostsFromSpecificList($gal_ids);
	    		break;
			case 'woocommerce':
				$arrPosts = $this->getProductsFromCategories($publishedOnly);
		    	break;
			default:
				RevSliderFunctions::throwError("getSlidesFromPosts error: This source type must be from posts.");
			    break;
		}
		
		$arrSlides = array();
		
		$templateKey = 0;
		$numTemplates = count($slideTemplates);
		
		
        foreach($arrPosts as $postData){
            if(empty($postData)) continue; //ignore empty entries, like from instagram

            $slideTemplate = clone($slideTemplates[$templateKey]);
			
			//advance the templates
			$templateKey++;
			if($templateKey == $numTemplates){
				$templateKey = 0;
				$slideTemplates = $this->getSlidesFromGallery($publishedOnly); //reset as clone did not work properly
				$slideTemplates = RevSliderFunctions::assocToArray($slideTemplates);
			}

			$slide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
			$slide->initByPostData($postData, $slideTemplate, $this->id);
			$arrSlides[] = $slide;
		}
		
		$this->arrSlides = $arrSlides;
		
		return($arrSlides);
	}
	
	
	/**
	 * get slides from posts
	 */
	public function getSlidesFromStream($publishedOnly = false){
		
		$slideTemplates = $this->getSlidesFromGallery($publishedOnly);
		$slideTemplates = RevSliderFunctions::assocToArray($slideTemplates);
		
		if(count($slideTemplates) == 0) return array();
		
		$arrPosts = array();
		
		$max_allowed = 999999;
		$sourceType = $this->getParam("source_type","gallery");
		$additions = array('fb_type' => 'album');
		switch($sourceType){
			case "facebook":
				$facebook = new RevSliderFacebook($this->_framework, $this->getParam('facebook-transient','1200'));
                if($this->getParam('facebook-type-source','timeline') == "album"){
                    $arrPosts = $facebook->get_photo_set_photos($this->getParam('facebook-album'),$this->getParam('facebook-count',10),$this->getParam('facebook-app-id'),$this->getParam('facebook-app-secret'));
                }
                else{
					$user_id = $facebook->get_user_from_url($this->getParam('facebook-page-url'));
					$arrPosts = $facebook->get_photo_feed($user_id,$this->getParam('facebook-app-id'),$this->getParam('facebook-app-secret'),$this->getParam('facebook-count',10));
                    $additions['fb_type'] = $this->getParam('facebook-type-source','timeline');
					$additions['fb_user_id'] = $user_id;
                }
				
				if(!empty($arrPosts)){
					foreach($arrPosts as $k => $p){
						if(!isset($p->status_type)) continue;
						
						if(in_array($p->status_type, array("wall_post"))) unset($arrPosts[$k]);
					}
				}
				$max_posts = $this->getParam('facebook-count', '25', self::FORCE_NUMERIC);
				$max_allowed = 25;
			break;
			case "twitter":
				$twitter = new RevSliderTwitter($this->_framework, $this->getParam('twitter-consumer-key'),$this->getParam('twitter-consumer-secret'),$this->getParam('twitter-access-token'),$this->getParam('twitter-access-secret'),$this->getParam('twitter-transient','1200'));
				$arrPosts = $twitter->get_public_photos($this->getParam('twitter-user-id'),$this->getParam('twitter-include-retweets'),$this->getParam( 'twitter-exclude-replies'),$this->getParam('twitter-count'),$this->getParam('twitter-image-only'));	
				$max_posts = $this->getParam('twitter-count', '500', self::FORCE_NUMERIC);
				$max_allowed = 500;
				$additions['twitter_user'] = $this->getParam('twitter-user-id');
			break;
			case "instagram":
				$instagram = new RevSliderInstagram($this->_framework, $this->getParam('instagram-transient','1200'));
				if($this->getParam('instagram-type','user')!="hash"){
                    $search_user_id = $this->getParam('instagram-user-id');
                    $arrPosts = $instagram->get_public_photos($search_user_id,$this->getParam('instagram-count'));
                }
                else{
                    $search_hash_tag = $this->getParam('instagram-hash-tag');
                    $arrPosts = $instagram->get_tag_photos($search_hash_tag,$this->getParam('instagram-count'));
                }
                
				$max_posts = $this->getParam('instagram-count', '33', self::FORCE_NUMERIC);
				$max_allowed = 33;
			break;
			case "flickr":
				$flickr = new RevSliderFlickr($this->_framework, $this->getParam('flickr-api-key'),$this->getParam('flickr-transient','1200'));
				switch($this->getParam('flickr-type')){
					case 'publicphotos':
						$user_id = $flickr->get_user_from_url($this->getParam('flickr-user-url'));
						$arrPosts = $flickr->get_public_photos($user_id,$this->getParam('flickr-count'));
					break;
					case 'gallery':
						$gallery_id = $flickr->get_gallery_from_url($this->getParam('flickr-gallery-url'));
						$arrPosts = $flickr->get_gallery_photos($gallery_id,$this->getParam('flickr-count'));
					break;
					case 'group':
						$group_id = $flickr->get_group_from_url($this->getParam('flickr-group-url'));
						$arrPosts = $flickr->get_group_photos($group_id,$this->getParam('flickr-count'));
					break;
					case 'photosets':
						$arrPosts = $flickr->get_photo_set_photos($this->getParam('flickr-photoset'),$this->getParam('flickr-count'));
					break;
				}
				$max_posts = $this->getParam('flickr-count', '99', self::FORCE_NUMERIC);
			break;
			case 'youtube':
				$channel_id = $this->getParam('youtube-channel-id');
				$youtube = new RevSliderYoutube($this->_framework, $this->getParam('youtube-api'),$channel_id,$this->getParam('youtube-transient','1200'));
				
				if($this->getParam('youtube-type-source')=="playlist"){
					$arrPosts = $youtube->show_playlist_videos($this->getParam('youtube-playlist'),$this->getParam('youtube-count'));
				}
				else{
					$arrPosts = $youtube->show_channel_videos($this->getParam('youtube-count'));
				}
				$additions['yt_type'] = $this->getParam('youtube-type-source','channel');
				$max_posts = $this->getParam('youtube-count', '25', self::FORCE_NUMERIC);
				$max_allowed = 50;
				break;
			case 'vimeo':
				$vimeo = new RevSliderVimeo($this->_framework, $this->getParam('vimeo-transient','1200'));
				$vimeo_type = $this->getParam('vimeo-type-source');
				
				switch ($vimeo_type) {
					case 'user':
						$arrPosts = $vimeo->get_vimeo_videos($vimeo_type,$this->getParam('vimeo-username'));
						break;
					case 'channel':
						$arrPosts = $vimeo->get_vimeo_videos($vimeo_type,$this->getParam('vimeo-channelname'));
						break;
					case 'group':
						$arrPosts = $vimeo->get_vimeo_videos($vimeo_type,$this->getParam('vimeo-groupname'));
						break;
					case 'album':
						$arrPosts = $vimeo->get_vimeo_videos($vimeo_type,$this->getParam('vimeo-albumid'));
						break;
					default:
						break;

				}
				$additions['vim_type'] = $this->getParam('vimeo-type-source','user');
				$max_posts = $this->getParam('vimeo-count', '25', self::FORCE_NUMERIC);
				$max_allowed = 60;
				break;
			default:
				RevSliderFunctions::throwError("getSlidesFromStream error: This source type must be from stream.");
			break;
		}
		
		if($max_posts < 0) $max_posts *= -1;
		
		$arrPosts = $this->_framework->apply_filters('revslider_pre_mod_stream_data', $arrPosts, $sourceType, $this->id);
		
		while(count($arrPosts) > $max_posts || count($arrPosts) > $max_allowed){
			array_pop($arrPosts);
		}
		
		$arrPosts = $this->_framework->apply_filters('revslider_post_mod_stream_data', $arrPosts, $sourceType, $this->id);
		
		$arrSlides = array();
		
		$templateKey = 0;
		$numTemplates = count($slideTemplates);
		
		if(empty($arrPosts)) RevSliderFunctions::throwError(__('Failed to load Stream'));
		
		foreach($arrPosts as $postData){
			$slideTemplate = $slideTemplates[$templateKey];
			
			//advance the templates
			$templateKey++;
			if($templateKey == $numTemplates)
				$templateKey = 0;
			
			$slide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
			$slide->initByStreamData($postData, $slideTemplate, $this->id, $sourceType, $additions);
			$arrSlides[] = $slide;
		}
		
		$this->arrSlides = $arrSlides;
		
		return($arrSlides);
	}
	
	
	/**
     * get the first slide ID of the current slider
     */
    public function getFirstSlideIdFromGallery(){
        $this->validateInited();

        $arrSlides = array();
        $arrSlideRecords = $this->db->fetch(RevSliderGlobals::$table_slides,$this->db->prepare("slider_id = %s", array($this->id)),"slide_order",'',' LIMIT 0,1');

        foreach ($arrSlideRecords as $record){
            $slide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
            $slide->initByData($record);
            $slideID = $slide->getID();
            $arrSlides[$slideID] = $slide;
            return $arrSlides;
        }

        return false;
    }


    /**
	 * get slides of the current slider
	 */
    public function getSlidesFromGallery($publishedOnly = false, $storeID = 0, $allwpml = false, $first = false){
	
		$this->validateInited();
		
		$arrSlides = array();
        $arrSlideRecords = $this->db->fetch(RevSliderGlobals::$table_slides,$this->db->prepare("slider_id = %s", array($this->id)),"slide_order");
		
		$arrChildren = array();

		foreach ($arrSlideRecords as $record){
			$slide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
			$slide->initByData($record);

			$slideID = $slide->getID();
			$arrIdsAssoc[$slideID] = true;

            $_arrStoreIDs = explode(',', $slide->getParam('store_id', 0) );
            if ( $storeID && ! ( in_array($storeID, $_arrStoreIDs) || in_array(0, $_arrStoreIDs) )) {
                continue;
            }

			if($publishedOnly == true){
				$state = $slide->getParam("state", "published");
				if($state == "unpublished"){
					continue;
				}
			}
			
			$parentID = $slide->getParam("parentid","");
			if(!empty($parentID)){
				$lang = $slide->getParam("lang","");
				if(!isset($arrChildren[$parentID]))
					$arrChildren[$parentID] = array();
				$arrChildren[$parentID][] = $slide;
				if(!$allwpml)
					continue;	//skip adding to main list
			}
			
			//init the children array
			$slide->setArrChildren(array());
			
			$arrSlides[$slideID] = $slide;

            if($first) break; //we only want the first slide!
		}
		
		//add children array to the parent slides
		foreach($arrChildren as $parentID=>$arr){
			if(!isset($arrSlides[$parentID])){
				continue;
			}
			$arrSlides[$parentID]->setArrChildren($arr);
		}
		
		$this->arrSlides = $arrSlides;

		return($arrSlides);
	}


    /**
     * get slides of the current slider
     * @since: 5.3.0
     */
    public function getSlidesFromGalleryRaw($publishedOnly = false){

        $this->validateInited();

        $arrSlideRecords = $this->db->fetch(RevSliderGlobals::$table_slides,$this->db->prepare("slider_id = %s", array($this->id)),"slide_order");

        return $arrSlideRecords;
    }


    /**
     * get slides of the current slider
     * @since: 5.3.0
     */
    public function getSlidesCountFromGallery($publishedOnly = false){

        $this->validateInited();

        $sqlSelect = $this->db->prepare("SELECT `id` FROM ".RevSliderGlobals::$table_slides." WHERE `slider_id` = %s", array($this->id));
        $arrSlideRecords = $this->db->runSqlR($sqlSelect);

        return $arrSlideRecords;
    }



    /**
	 * 
	 * get slide id and slide title from gallery
	 */
	public function getArrSlidesFromGalleryShort(){
		$arrSlides = $this->getSlidesFromGallery();
		
		$arrOutput = array();
		$counter = 0;
		foreach($arrSlides as $slide){
			$slideID = $slide->getID();
			$outputName = 'Slide '.$counter;
			$title = $slide->getParam('title','');
			$counter++;
			
			if(!empty($title))
				$outputName .= ' - ('.$title.')';
				
			$arrOutput[$slideID] = $outputName;
		}
		
		return($arrOutput);
	}
	
	
	/**
	 * 
	 * get slides for output
	 * one level only without children
	 */
    public function getSlidesForOutput($publishedOnly = false, $storeID = 0, $lang = 'all',$gal_ids = array()){
		
		$isSlidesFromPosts = $this->isSlidesFromPosts();
		$isSlidesFromStream = $this->isSlidesFromStream();
		
		if($isSlidesFromPosts){
            $arrParentSlides = $this->getSlidesFromPosts($publishedOnly, $gal_ids);
		}elseif($isSlidesFromStream !== false){
			$arrParentSlides = $this->getSlidesFromStream($publishedOnly);
		}else{
			$arrParentSlides = $this->getSlides($publishedOnly, $storeID);
		}
		
		if($lang == 'all' || $isSlidesFromPosts || $isSlidesFromStream)
			return($arrParentSlides);
		
		$arrSlides = array();
		foreach($arrParentSlides as $parentSlide){
			$parentLang = $parentSlide->getLang();
			if($parentLang == $lang)
				$arrSlides[] = $parentSlide;
				
			$childAdded = false;
			$arrChildren = $parentSlide->getArrChildren();
			foreach($arrChildren as $child){
				$childLang = $child->getLang();
				if($childLang == $lang){
					$arrSlides[] = $child;
					$childAdded = true;
					break;
				}
			}
			
			if($childAdded == false && $parentLang == "all")
				$arrSlides[] = $parentSlide;
		}
		
		return($arrSlides);
	}
	
	
	/**
	 * 
	 * get array of slide names
	 */
	public function getArrSlideNames(){
		if(empty($this->arrSlides))
			$this->getSlidesFromGallery();
		
		$arrSlideNames = array();

		foreach($this->arrSlides as $number=>$slide){
			$slideID = $slide->getID();
			$filename = $slide->getImageFilename();	
			$slideTitle = $slide->getParam("title","Slide");
			$slideName = $slideTitle;
			if(!empty($filename))
				$slideName .= " ($filename)";
			
			$arrChildrenIDs = $slide->getArrChildrenIDs();
			 
			$arrSlideNames[$slideID] = array("name"=>$slideName,"arrChildrenIDs"=>$arrChildrenIDs,"title"=>$slideTitle);
		}
		return($arrSlideNames);
	}
	
	
	/**
	 * 
	 * get array of slides numbers by id's
	 */
	public function getSlidesNumbersByIDs($publishedOnly = false){
		
		if(empty($this->arrSlides))
			$this->getSlides($publishedOnly);
		
		$arrSlideNumbers = array();
		
		$counter = 0;
		
		if(empty($this->arrSlides)) return $arrSlideNumbers;
		
		foreach($this->arrSlides as $slide){
			$counter++;
			$slideID = $slide->getID();
			$arrSlideNumbers[$slideID] = $counter;				
		}
		return($arrSlideNumbers);
	}
	
	
	/**
	 * 
	 * get slider params for export slider
	 */
	private function getParamsForExport(){
		$exportParams = $this->arrParams;
		
		//modify background image
		$urlImage = RevSliderFunctions::getVal($exportParams, "background_image");
		if(!empty($urlImage))
			$exportParams["background_image"] = $urlImage;
		
		return($exportParams);
	}

	
	/**
	 * 
	 * get slides for export
	 */
	public function getSlidesForExport($useDummy = false){
		$arrSlides = $this->getSlidesFromGallery(false, 0, true);
		$arrSlidesExport = array();
		
		foreach($arrSlides as $slide){
			$slideNew = array();
			$slideNew["id"] = $slide->getID();
			$slideNew["params"] = $slide->getParamsForExport();
			$slideNew["slide_order"] = $slide->getOrder();
			$slideNew["layers"] = $slide->getLayersForExport($useDummy);
			$slideNew["settings"] = $slide->getSettings();

			$arrSlidesExport[] = $slideNew;
		}

        return $this->_framework->apply_filters('revslider_getSlidesForExport', $arrSlidesExport);
	}


	/**
	 *
	 * get slides for export
	 */
	public function getStaticSlideForExport($useDummy = false){
		$arrSlidesExport = array();
		
		$slide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
		
		$staticID = $slide->getStaticSlideID($this->id);
		if($staticID !== false){
			$slideNew = array();
			$slide->initByStaticID($staticID);
			$slideNew["params"] = $slide->getParamsForExport();
			$slideNew["slide_order"] = $slide->getOrder();
			$slideNew["layers"] = $slide->getLayersForExport($useDummy);
			$slideNew["settings"] = $slide->getSettings();
			$arrSlidesExport[] = $slideNew;
		}

        return $this->_framework->apply_filters('revslider_getSlidesForExport', $arrSlidesExport);
	}
	
	
	/**
	 * get slides number
	 */
	public function getNumSlides($publishedOnly = false){

		if($this->arrSlides == null)
			$this->getSlides($publishedOnly);

		$numSlides = count($this->arrSlides);
		return($numSlides);
	}


	/**
     * get slides number
     */
    public function getNumSlidesRaw($publishedOnly = false){

        if($this->arrSlides == null){
            $ret = $this->getSlidesCountRaw($publishedOnly);
            $numSlides = count($ret);
        }else{
            $numSlides = count($this->arrSlides);
        }
        return($numSlides);
    }


    /**
	 * get real slides number, from posts, social streams ect.
	 */
	public function getNumRealSlides($publishedOnly = false, $type = 'post'){
		$numSlides = count($this->arrSlides);

		switch($type){
			case 'post':
                if($this->getParam('fetch_type', 'cat_tag') == 'next_prev'){
                    $numSlides = 2;
                }else{
                    $numSlides = $this->getParam('max_slider_posts', count($this->arrSlides));
                    if(intval($numSlides) == 0) $numSlides = '∞';
                }
			break;
			case 'facebook':
				$numSlides = $this->getParam('facebook-count', count($this->arrSlides));
			break;
			case 'twitter':
				$numSlides = $this->getParam('twitter-count', count($this->arrSlides));
			break;
			case 'instagram':
				$numSlides = $this->getParam('instagram-count', count($this->arrSlides));
			break;
			case 'flickr':
				$numSlides = $this->getParam('flickr-count', count($this->arrSlides));
			break;
			case 'youtube':
				$numSlides = $this->getParam('youtube-count', count($this->arrSlides));
			break;
			case 'vimeo':
				$numSlides = $this->getParam('vimeo-count', count($this->arrSlides));
			break;
		}

		return($numSlides);
	}


	/**
	 * get real slides number, from posts, social streams ect.
	 */
	public function getNumRealStreamSlides($publishedOnly = false){

		$this->getSlidesFromStream($publishedOnly);

		$numSlides = count($this->arrSlides);
		return($numSlides);
	}


	/**
	 * get sliders array - function don't belong to the object!
	 */
	public function getArrSliders($orders = false, $templates = 'neither'){
		$order_fav = false;
		if($orders !== false && key($orders) != 'favorite'){
			$order_direction = reset($orders);
			$do_order = key($orders);
		}else{
			$do_order = 'id';
			$order_direction = 'ASC';
			if(is_array($orders) && key($orders) == 'favorite'){
				$order_direction = reset($orders);
				$order_fav = true;
			}
		}
        //$where = "`type` != 'template' ";
        $where = "`type` != 'template' OR `type` IS NULL";

		$response = $this->db->fetch(RevSliderGlobals::$table_sliders,$where,$do_order,'',$order_direction);

		$arrSliders = array();
		foreach($response as $arrData){
			$slider = new RevSliderSlider($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
			$slider->initByDBData($arrData);

			/*
			This part needs to stay for backwards compatibility. It is used in the update process from v4x to v5x
			*/
			if($templates === true){
				if($slider->getParam("template","false") == "false") continue;
			}elseif($templates === false){
				if($slider->getParam("template","false") == "true") continue;
			}

			$arrSliders[] = $slider;
		}

		if($order_fav === true){
			$temp = array();
			$temp_not = array();
			foreach($arrSliders as $key => $slider){
				if($slider->isFavorite()){
					$temp_not[] = $slider;
				}else{
					$temp[] = $slider;
				}
			}
			$arrSliders = array();
			$arrSliders = ($order_direction == 'ASC') ? array_merge($temp, $temp_not) : array_merge($temp_not, $temp);
		}

		return($arrSliders);
	}


	/**
	 * get array of alias
	 */
	public function getAllSliderAliases(){
		$where = "`type` != 'template'";

		$response = $this->db->fetch(RevSliderGlobals::$table_sliders,$where,"id");

		$arrAliases = array();
		foreach($response as $arrSlider){
			$arrAliases[] = $arrSlider["alias"];
		}

		return($arrAliases);
	}

	/**
	 * get array of alias
	 */
	public function getAllSliderForAdminMenu(){
		$arrSliders = $this->getArrSliders();
		$arrShort = array();
		if(!empty($arrSliders)){
			foreach($arrSliders as $slider){
				$id = $slider->getID();
				$title = $slider->getTitle();
				$alias = $slider->getAlias();

				$arrShort[$id] = array('title' => $title, 'alias' => $alias);
			}
		}

		return($arrShort);
	}


	/**
	 *
	 * get array of slider id -> title
	 */
	public function getArrSlidersShort($exceptID = null,$filterType = self::SLIDER_TYPE_ALL){
		$arrSliders = $this->getArrSliders();
		$arrShort = array();
		foreach($arrSliders as $slider){
			$id = $slider->getID();
			$isFromPosts = $slider->isSlidesFromPosts();
			$isTemplate = $slider->getParam("template","false");

			//filter by gallery only
			if($filterType == self::SLIDER_TYPE_POSTS && $isFromPosts == false)
				continue;

			if($filterType == self::SLIDER_TYPE_GALLERY && $isFromPosts == true)
				continue;

			//filter by template type
			if($filterType == self::SLIDER_TYPE_TEMPLATE && $isFromPosts == false)
				continue;

			//filter by except
			if(!empty($exceptID) && $exceptID == $id)
				continue;

			$title = $slider->getTitle();
			$arrShort[$id] = $title;
		}
		return($arrShort);
	}

	/**
	 *
	 * get array of sliders with slides, short, assoc.
	 */
	public function getArrSlidersWithSlidesShort($filterType = self::SLIDER_TYPE_ALL){
		$arrSliders = self::getArrSlidersShort(null, $filterType);

		$output = array();
		foreach($arrSliders as $sliderID=>$sliderName){
			$slider = new RevSliderSlider($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
			$slider->initByID($sliderID);

			$isFromPosts = $slider->isSlidesFromPosts();
			$isTemplate = $slider->getParam("template","false");

			//filter by gallery only
			if($filterType == self::SLIDER_TYPE_POSTS && $isFromPosts == false)
				continue;

			if($filterType == self::SLIDER_TYPE_GALLERY && $isFromPosts == true)
				continue;

			//filter by template type
			if($filterType == self::SLIDER_TYPE_TEMPLATE && $isFromPosts == false) //$isTemplate == "false")
				continue;
				
			$sliderTitle = $slider->getTitle();
			$arrSlides = $slider->getArrSlidesFromGalleryShort();

			foreach($arrSlides as $slideID=>$slideName){
				$output[$slideID] = $sliderName.", ".$slideName;
			}
		}

		return($output);
	}


	/**
	 *
	 * get max order
	 */
	public function getMaxOrder(){
		$this->validateInited();
		$maxOrder = 0;
        $arrSlideRecords = $this->db->fetch(RevSliderGlobals::$table_slides,$this->db->prepare("slider_id = %s", array($this->id)),"slide_order desc","","limit 1");
		if(empty($arrSlideRecords))
			return($maxOrder);
		$maxOrder = $arrSlideRecords[0]["slide_order"];
		
		return($maxOrder);
	}
	
	/**
	 * 
	 * get setting - start with slide
	 */
	public function getStartWithSlideSetting(){
		
		$numSlides = $this->getNumSlides();
		
		$startWithSlide = $this->getParam("start_with_slide","1");
		if(is_numeric($startWithSlide)){
			$startWithSlide = (int)$startWithSlide - 1;
			if($startWithSlide < 0)
				$startWithSlide = 0;
				
			if($startWithSlide >= $numSlides)
				$startWithSlide = 0;
			
		}else
			$startWithSlide = 0;
		
		return($startWithSlide);
	}
	
	
	/**
	 * return if the slides source is from posts
	 */
	public function isSlidesFromPosts(){
		$this->validateInited();
		$sourceType = $this->getParam("source_type","gallery");
        if($sourceType == "posts" || $sourceType == "specific_posts" || $sourceType == "current_post" || $sourceType == "woocommerce")
			return(true);
		
		return(false);
	}
	
	
	/**
	 * return if the slides source is from stream
	 */
	public function isSlidesFromStream(){
		$this->validateInited();
		$sourceType = $this->getParam("source_type","gallery");
        if($sourceType != "posts" && $sourceType != "specific_posts" && $sourceType != "current_post" && $sourceType != "woocommerce" && $sourceType != "gallery")
			return($sourceType);
		
		return(false);
	}
	
	
	/**
	 * 
	 * get posts from categories (by the slider params).
	 */
	private function getPostsFromCategories($publishedOnly = false){
		$this->validateInited();
		
		$catIDs = $this->getParam("post_category");
		$data = RevSliderFunctionsWP::getCatAndTaxData($catIDs);
		
		$taxonomies = $data["tax"];
		$catIDs = $data["cats"];
		
		$sortBy = $this->getParam("post_sortby",self::DEFAULT_POST_SORTBY);
		$sortDir = $this->getParam("posts_sort_direction",self::DEFAULT_POST_SORTDIR);
		$maxPosts = $this->getParam("max_slider_posts","30");
		if(empty($maxPosts) || !is_numeric($maxPosts))
			$maxPosts = -1;
		
		$postTypes = $this->getParam("post_types","any");
			
		//set direction for custom order
		if($sortBy == RevSliderFunctionsWP::SORTBY_MENU_ORDER)
			$sortDir = RevSliderFunctionsWP::ORDER_DIRECTION_ASC;
		
		//Events integration
		$arrAddition = array();
		if($publishedOnly == true)			
			$arrAddition["post_status"] = RevSliderFunctionsWP::STATE_PUBLISHED;
		
		$slider_id = $this->getID();
		$arrPosts = RevSliderFunctionsWP::getPostsByCategory($slider_id, $catIDs,$sortBy,$sortDir,$maxPosts,$postTypes,$taxonomies,$arrAddition);
		
		return($arrPosts);
	}  
	
	
	/**
     * get related posts from current one
     * @since: 5.1.1
     */
    public function getPostsFromRelated(){
        $my_posts = array();
        
        $sortBy = $this->getParam("post_sortby",self::DEFAULT_POST_SORTBY);
        $sortDir = $this->getParam("posts_sort_direction",self::DEFAULT_POST_SORTDIR);
        $max_posts = $this->getParam("max_slider_posts","30");
        if(empty($max_posts) || !is_numeric($max_posts))
            $max_posts = -1;
        
        $post_id = get_the_ID();
        
        $tags_string = '';
        $post_tags = get_the_tags();
        
        if ($post_tags) {
            foreach ($post_tags as $post_tag) {
                $tags_string .= $post_tag->slug . ',';
            }
        }
        
        $query = array(
                        'exclude' => $post_id,
                        'numberposts' => $max_posts,
                        'order' => $sortDir,
                        'tag' => $tags_string
                      );
                      
        if(strpos($sortBy, "meta_num_") === 0){
            $metaKey = str_replace("meta_num_", "", $sortBy);
            $query["orderby"] = "meta_value_num";
            $query["meta_key"] = $metaKey;
        }else
        if(strpos($sortBy, "meta_") === 0){
            $metaKey = str_replace("meta_", "", $sortBy);
            $query["orderby"] = "meta_value";
            $query["meta_key"] = $metaKey;
        }else
            $query["orderby"] = $sortBy;
        
        $get_relateds = $this->_framework->apply_filters('revslider_get_related_posts', $query, $post_id);
        
        $tag_related_posts = get_posts($get_relateds);        
        
        
        if(count($tag_related_posts) < $max_posts){
            $ignore = array();
            foreach($tag_related_posts as $tag_related_post){
                $ignore[] = $tag_related_post->ID;
            }
            $article_categories = get_the_category($post_id);
            $category_string = '';
            foreach($article_categories as $category) { 
                $category_string .= $category->cat_ID . ',';
            }
            $max = $max_posts - count($tag_related_posts);
            
            $excl = implode(',', $ignore);
            $query = array(
                            'exclude' => $excl,
                            'numberposts' => $max,
                            'category' => $category_string
                          );
                          
            if(strpos($sortBy, "meta_num_") === 0){
                $metaKey = str_replace("meta_num_", "", $sortBy);
                $query["orderby"] = "meta_value_num";
                $query["meta_key"] = $metaKey;
            }else
            if(strpos($sortBy, "meta_") === 0){
                $metaKey = str_replace("meta_", "", $sortBy);
                $query["orderby"] = "meta_value";
                $query["meta_key"] = $metaKey;
            }else
                $query["orderby"] = $sortBy;
            
            $get_relateds = $this->_framework->apply_filters('revslider_get_related_posts', $query, $post_id);
            $cat_related_posts = get_posts($get_relateds);
            
            $tag_related_posts = $tag_related_posts + $cat_related_posts;
        }
        
        foreach($tag_related_posts as $post){
            $the_post = array();
            
            if(method_exists($post, "to_array"))
                $the_post = $post->to_array();
            else
                $the_post = (array)$post;
            
            if($the_post['ID'] == $post_id) continue;
            
            $my_posts[] = $the_post;
        }
        
        return $my_posts;
    }
    
    
    /**
     * get popular posts
     * @since: 5.1.1
     */
    public function getPostsFromPopular($max_posts = false){
		return array();
        $post_id = get_the_ID();
        
        if($max_posts == false){
            $max_posts = $this->getParam("max_slider_posts","30");
            if(empty($max_posts) || !is_numeric($max_posts))
                $max_posts = -1;
        }else{
            $max_posts = intval($max_posts);
        }
        $my_posts = array();
        
        $args = array(
            'post_type' => 'any',
            'posts_per_page' => $max_posts,
            'suppress_filters' => 0,
            'meta_key'    => '_thumbnail_id',
            'orderby'     => 'comment_count',
            'order'       => 'DESC'
        );
        
        $args = $this->_framework->apply_filters('revslider_get_popular_posts', $args, $post_id);
        $posts = get_posts($args);
        
        foreach($posts as $post){
        
            if(method_exists($post, "to_array"))
                $my_posts[] = $post->to_array();
            else
                $my_posts[] = (array)$post;
        }
        
        return $my_posts;
    }
    
    
    /**
     * get recent posts
     * @since: 5.1.1
     */
    public function getPostsFromRecent($max_posts = false){
		return array();
        $post_id = get_the_ID();
        
        if($max_posts == false){
            $max_posts = $this->getParam("max_slider_posts","30");
            if(empty($max_posts) || !is_numeric($max_posts))
                $max_posts = -1;
        }else{
            $max_posts = intval($max_posts);
        }
        
        $my_posts = array();
        
        $args = array(
            'post_type' => 'any',
            'posts_per_page' => $max_posts,
            'suppress_filters' => 0,
            'meta_key'    => '_thumbnail_id',
            'orderby'     => 'date',
            'order'       => 'DESC'
        );
        $args = $this->_framework->apply_filters('revslider_get_latest_posts', $args, $post_id);
        
        $posts = get_posts($args);
        
        foreach($posts as $post){
        
            if(method_exists($post, "to_array"))
                $my_posts[] = $post->to_array();
            else
                $my_posts[] = (array)$post;
        }
        
        return $my_posts;
    }
    
    /**
     * get recent posts
     * @since: 5.1.1
     */
    public function getPostsNextPrevious(){
        $my_posts = array();
        
        $startup_next_post = get_next_post();
        if (!empty( $startup_next_post )){
            if(method_exists($startup_next_post, "to_array"))
                $my_posts[] = $startup_next_post->to_array();
            else
                $my_posts[] = (array)$startup_next_post;
        }    

        $startup_previous_post = get_previous_post();
        if (!empty( $startup_previous_post )){
            if(method_exists($startup_previous_post, "to_array"))
                $my_posts[] = $startup_previous_post->to_array();
            else
                $my_posts[] = (array)$startup_previous_post;
        }
        
        return $my_posts;
    }
    
    
    /**
	 * get products from categories (by the slider params).
	 * @since: 5.1.0
	 */
	private function getProductsFromCategories($publishedOnly = false){
		$this->validateInited();
		
		$catIDs = $this->getParam("product_category");
		$data = RevSliderFunctionsWP::getCatAndTaxData($catIDs);
		
		$taxonomies = $data["tax"];
		$catIDs = $data["cats"];
		
		$sortBy = $this->getParam("product_sortby",self::DEFAULT_POST_SORTBY);
		$sortDir = $this->getParam("product_sort_direction",self::DEFAULT_POST_SORTDIR);
		$maxPosts = $this->getParam("max_slider_products","30");
		if(empty($maxPosts) || !is_numeric($maxPosts))
			$maxPosts = -1;
		
		$postTypes = $this->getParam("product_types","any");
			
		//set direction for custom order
		if($sortBy == RevSliderFunctionsWP::SORTBY_MENU_ORDER)
			$sortDir = RevSliderFunctionsWP::ORDER_DIRECTION_ASC;
		
		//Events integration
		$arrAddition = array();
		if($publishedOnly == true)			
			$arrAddition["post_status"] = RevSliderFunctionsWP::STATE_PUBLISHED;
		
		$slider_id = $this->getID();
		$arrPosts = RevSliderFunctionsWP::getPostsByCategory($slider_id, $catIDs,$sortBy,$sortDir,$maxPosts,$postTypes,$taxonomies,$arrAddition);
		
		return($arrPosts);
	}
	
	
	/**
	 * 
	 * get posts from specific posts list
	 */
    private function getPostsFromSpecificList($gal_ids = array()){
		
        $is_gal = false;
        $additional = array();

        if(!empty($gal_ids) && $gal_ids[0]){
            $strPosts = $gal_ids;
            $strPosts = $this->_framework->apply_filters('revslider_set_posts_list_gal', $strPosts, $this->getID());
            $is_gal = true;
        }else{
            if(isset($gal_ids[0])){
                unset($gal_ids[0]);
                $strPosts = implode(",", $gal_ids);
                $additional['order'] = "none";
                $additional['orderby'] = "post__in";
            }else {
                $strPosts = $this->getParam("posts_list", "");    
                $additional['order'] = $this->getParam("posts_sort_direction", "DESC");
                $additional['orderby'] = $this->getParam("post_sortby", "");
            }
            $strPosts = $this->_framework->apply_filters('revslider_set_posts_list', $strPosts, $this->getID());
        }
		
		$slider_id = $this->getID();
		
        $arrPosts = RevSliderFunctionsWP::getPostsByIDs($strPosts, $slider_id, $is_gal, $additional);

		return($arrPosts);
	}
	
	/**
	 * update sortby option
	 */
	public function updatePostsSortbyFromData($data){
		
		$sliderID = RevSliderFunctions::getVal($data, "sliderID");
		$sortBy = RevSliderFunctions::getVal($data, "sortby");
		RevSliderFunctions::validateNotEmpty($sortBy,"sortby");
		
		$this->initByID($sliderID);
		$arrUpdate = array();
		$arrUpdate["post_sortby"] = $sortBy;
		
		$this->updateParam($arrUpdate); 
	}

	/**
	 * 
	 * replace image urls
	 */
	public function replaceImageUrlsFromData($data){
		
		$sliderID = RevSliderFunctions::getVal($data, "sliderid");
		$urlFrom = RevSliderFunctions::getVal($data, "url_from");
		RevSliderFunctions::validateNotEmpty($urlFrom,"url from");
		$urlTo = RevSliderFunctions::getVal($data, "url_to");
		$replaceAll = RevSliderFunctions::getVal($data, "replace_all");
        if ($replaceAll == 'on') {
            $allSliders = $this->getArrSlidersShort();
            foreach ($allSliders as $id => $title) {
                $this->initByID($id);
                $arrSildes = $this->getSlides();
                foreach($arrSildes as $slide){
                    $slide->replaceImageUrls($urlFrom, $urlTo);
                }

                $slide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
                $staticID = $slide->getStaticSlideID($id);

                if($staticID !== false){
                    $slide->initByStaticID($staticID);
                    $slide->replaceImageUrls($urlFrom, $urlTo, $staticID);
                }
            }
        } else {
            $this->initByID($sliderID);
            
            $arrSildes = $this->getSlides();
            foreach($arrSildes as $slide){
                $slide->replaceImageUrls($urlFrom, $urlTo);
            }

            $slide = new RevSliderSlide($this->_framework, $this->_query, $this->_curl, $this->_filesystem, $this->_images, $this->_resource, $this->_googleFonts, $this->_registerHelper);
            $staticID = $slide->getStaticSlideID($sliderID);

            if($staticID !== false){
                $slide->initByStaticID($staticID);
                $slide->replaceImageUrls($urlFrom, $urlTo, $staticID);
            }
        }
	}
	
	public function resetSlideSettings($data){
		$sliderID = RevSliderFunctions::getVal($data, "sliderid");
		
		$this->initByID($sliderID);
		
		$arrSildes = $this->getSlides();
		foreach($arrSildes as $slide){
			$slide->reset_slide_values($data);
		}
	}
	
	public static function clear_error_in_string($m){
		return 's:'.strlen($m[2]).':"'.$m[2].'";';
	}
	
}
