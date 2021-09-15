<?php
namespace Wanexo\Mdlnavi\Block;

use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Topmenu;
use Magento\Catalog\Model\CategoryFactory;
class Mdlnavi extends Topmenu 
{
	
	/* Function to get home url */
	public function getHomeUrl()
	{
		$currentStore = $this->_storeManager->getStore();
		$mediaPath = $currentStore->getBaseUrl();
		return $mediaPath;
	}
	
	/* Function to initialize instance of object manager */
	
	public function getObjectManager($path)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$ob = $objectManager->get($path)->create();
		return $ob;
	}
	
	/* Function to initialize instance of object manager */
	
	public function getCreateOM($path)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$ob = $objectManager->create($path);
		return $ob;
	}
	
	/* Funtion to initialize category attributes */
	public function getNavData($id)
	{
		$objectManager = $this->getObjectManager('Magento\Catalog\Model\CategoryFactory');
		$helper = $objectManager->load(str_replace('category-node-', '', $id));
		
		$mediaPath = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		
		$helper_data = array();
		$helper_data['label'] = $helper->getWanLabel();
		$helper_data['labelcolor'] = $helper->getWanLabelcolor();
		$helper_data['navtype'] = $helper->getWanNavtype();
		$helper_data['subcat'] = $helper->getWanSubcat();
		$helper_data['topblock'] = $helper->getWanTopblock();
		$helper_data['bottomblock'] = $helper->getWanBottomblock();
		$helper_data['rightblock'] = $helper->getWanRightblock();
		$helper_data['nocol'] = $helper->getWanNocol();
		$helper_data['rblockwidth'] = $helper->getWanRblockwidth();
		$helper_data['mblockwidth'] = $helper->getWanMblockwidth();
		$helper_data['thumb'] = $helper->getWanThumbnail();
		$helper_data['icon'] = $helper->getWanIcon();
		$helper_data['bgimg'] = $helper->getBgimage();
		$helper_data['bgposition'] = $helper->getBgPosition();
		$helper_data['customlink'] = $helper->getCustomLink();
		$helper_data['wanclink'] = $helper->getWanClink();
		$helper_data['wanpage'] = $helper->getWanPage();
		$helper_data['caturl'] = $helper->getUrl();
		$helper_data['mediapath'] = $mediaPath;
		return $helper_data;
	}
	 protected function _getHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        $colBrakes = []
    ) { 
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';
		$rowPerCol='';
		$ncol = $limit;
        foreach ($children as $child) {
			$childClass = '';
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();
			
			$megamenuData = array(
				'type' => '',
				'labelcolor' => '',
				'menu' => 1,
				'top' => '',
				'bottom' => '',
				'right' => '',
				'rsize' => 0,
				'thumb' =>'',
			);
			$menuData = $this->getNavData($child->getId());
			
			$megamenuData['labelcolor'] = $menuData['labelcolor'];
			$megamenuData['mdllabel'] = $menuData['label'];

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }
			
			if ($childLevel == 0 ) {
				$childClass .= ' '. $menuData['navtype'];
				$rowPerCol = $menuData['nocol'];
				$megamenuData['type'] =  $menuData['navtype'];
				$megamenuData['labelcolor'] = $menuData['labelcolor'];
				$megamenuData['mdllabel'] = $menuData['label'];
				$megamenuData['menu'] = $menuData['subcat'];
				$megamenuData['top'] = $menuData['topblock'];
				$megamenuData['bottom'] = $menuData['bottomblock'];
				$megamenuData['right'] = $menuData['rightblock'];
				$megamenuData['rsize'] = $menuData['rblockwidth'];
				$megamenuData['msize'] = $menuData['mblockwidth'];
				$megamenuData['thumb'] = $menuData['thumb'];
				$megamenuData['icon'] = $menuData['icon'];
				$megamenuData['bgimg'] = $menuData['bgimg'];
				$megamenuData['bgposition'] = $menuData['bgposition'];
				if ( $megamenuData['menu'] == '' ) $megamenuData['menu'] = 1;
				if ( $megamenuData['rsize'] == '' ) $megamenuData['rsize'] = 0;
			}
			else
			{
				$megamenuData['thumb'] = $menuData['thumb'];
			}
			$showChildren = false;
			$leftClass = $rightClass = $top = $bottom = $right = $menu = '';
			if (
				$child->hasChildren()
				|| ( $childLevel == 0 && $megamenuData['type'] == 'megamenu'
					&& ( !empty($megamenuData['top']) || !empty($megamenuData['bottom'])
						|| ( !empty($megamenuData['right']) && $megamenuData['rsize'] != 0 )
					)
				)
			) {
				$showChildren = true;
				if ( $megamenuData['type'] == 'megamenu' ) {
					$processFilter = $this->getCreateOM('Wanexo\Mdlnavi\Model\Processor');
					$top = $processFilter->content($megamenuData['top']);
					$bottom = $processFilter->content($megamenuData['bottom']);
					$right = $processFilter->content($megamenuData['right']);
					$rsize = $megamenuData['rsize'];
					$msize=$megamenuData['msize'];
				}
				if ( $megamenuData['menu'] == 1 || $megamenuData['type'] != 'megamenu' ) {
					if ($megamenuData['thumb']) {
						//$menu .='<span class="cat_thumb"><a href="'.$menuData['caturl'].'"><img src="'.$menuData['mediapath'].'catalog/category/'.$megamenuData['thumb'].'" alt=""/></a></span>';
					}
					$colStops = null;
					$menu .= '<ul class="level' . $childLevel . ' submenu">';
					$menu .= $this->_getHtml($child, $childrenWrapClass, $rowPerCol);
					$menu .= '</ul>';
				}
				if ( !$child->hasChildren() || $megamenuData['menu'] != 1 ) {
					$childClass .= ' parent parent-fake';
				}				
			}
			$child->setClass($childClass);
            if (count($colBrakes) && $colBrakes[$counter]['colbrake']) {
                $html .= '</ul></li><li class="column"><ul>';
            }

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
			if($menuData['customlink']!='')
			{
				if($menuData['customlink']=='links')
				{
					$cUrl = $menuData['wanclink'];
				}
				else
				{
					$cUrl = $menuData['wanpage'];
				}
			}
			else
			{
				$cUrl = $menuData['caturl'];
			}
			if ($megamenuData['thumb'] &&  $childLevel > 0) {
				
					$html .='<span class="cat_thumb"><a href="'.$cUrl.'"><img src="'.$menuData['mediapath'].'catalog/category/'.$megamenuData['thumb'].'" alt=""/></a></span>';
			}
            $html .= '<a href="' . $cUrl . '" ' . $outermostClassCode . '>';
			if (!empty($megamenuData['icon'])) {
					$html.='<span class="mdlicon"><img src="'.$menuData['mediapath'].'catalog/category/'.$megamenuData['icon'].'" alt=""/></span>';
				}
			$html .='<span>' . $this->escapeHtml($child->getName()) . '</span>';
			if (!empty($megamenuData['mdllabel'])) {
					$html.='<span class="mdlabel lab-'.$megamenuData['labelcolor'].'">'.$megamenuData['mdllabel'].'</span>';
				}
			
			$html .= '</a>';
			if ( $showChildren ) {
				if (!empty($childrenWrapClass)) {
					if($rowPerCol){ $rpc = ' col-block-'.$rowPerCol.' main-width-'.$msize;} else { $rpc = ''; }
					
					if (!empty($megamenuData['bgimg'])) {
						$bgClass = $megamenuData['bgposition'];
						$bgImage = 'style="background:url(\"'.$menuData['mediapath'].'catalog/category'.$megamenuData['bgimg'].'\")"';
					}
					else
					{
						$bgClass = '';
						$bgImage = '';
					}
					if (!empty($megamenuData['bgimg'])) {
					$html .= '<div style="background-image:url('.$menuData['mediapath'].'catalog/category/'.$megamenuData['bgimg'].')" class="'.$bgClass.' '. $childrenWrapClass . ' '.$rpc.'">';
					}
					else
					{
						if($megamenuData['type'] == 'megamenu')
						{
							$html .= '<div class="'. $childrenWrapClass . ' '.$rpc.'">';
						}
					}
				}
				if ( $childLevel == 0 && $megamenuData['type'] == 'megamenu' ) {
					$centerColumn = '';
					switch ( $rsize ) {
						case  1:
						if (empty($megamenuData['right'])) {
							$centerColumn = '<div class="col-sm-12">'.$menu.'</div>';
						}else{
							$centerColumn = '<div class="col-sm-8">'.$menu.'</div><div class="col-sm-4 menu-content">'.$right.'</div>';
						}
							break;
						case  2:
						if (empty($megamenuData['right'])) {
							$centerColumn = '<div class="col-sm-12">'.$menu.'</div>';
						}else{
							$centerColumn = '<div class="col-sm-6">'.$menu.'</div> <div class="col-sm-6 menu-content">'.$right.'</div>';
						}
							
							break;
						case '3' :
						if (empty($megamenuData['right'])) {
							$centerColumn = '<div class="col-sm-12">'.$menu.'</div>';
						}else{
							$centerColumn = '<div class="col-sm-4">'.$menu.'</div> <div class="col-sm-8 menu-content">'.$right.'</div>';
						}
							
							break;
						case '4' :
						if (empty($megamenuData['right'])) {
							$centerColumn = '<div class="col-sm-12">'.$menu.'</div>';
						}else{
							$centerColumn = '<div class="col-sm-12">'.$menu.'</div> <div class="col-sm-12 menu-content">'.$right.'</div>';
						}
							
							break;	
						default :
							if (empty($megamenuData['right'])) {
								$centerColumn = '<div class="col-sm-12">'.$menu.'</div>';
							}else{
								$centerColumn = '<div class="col-sm-9">'.$menu.'</div><div class="col-sm-3 menu-content">'.$right.'</div>';
							}
					}
					if (!empty($top)) {
								$html .= '<div class="topBlock  menu-content">'.$top.'</div>';
							}
							$html .= '<div class="row '.$rsize.'">';
							$html .= $centerColumn;
							$html .= '</div>';
							
							if (!empty($bottom)) {
								$html .= '<div class="bottomBlock menu-content">'.$bottom.'</div>';
							}
				}  else {
					$html .= $menu;
				}
				if (!empty($childrenWrapClass)) {
					if($megamenuData['type'] == 'megamenu')
					{
						$html .= '</div>';
					}
				}
			}
			$html .= '</li>';
			if($childLevel==1) {
				if($ncol==0)
				{
					$ncol=1;
				}
				if($counter%$ncol==0){
					 if($childrenCount>$counter){
							//$html .='</ul><ul class="level0 '.$childLevel.'">';
						}
				}
				}
            $itemPosition++;
            $counter++;
        }

       

        return $html;
    }
	
	/*protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        if (!$child->hasChildren()) {
            return $html;
        }

        $colStops = null;
        if ($childLevel == 0 && $limit) {
           // $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }

        $html .= '<ul class="level' . $childLevel . ' submenu">';
        $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
        $html .= '</ul>';

        return $html;
    }*/
	
	public function getMobileHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_before',
            ['menu' => $this->_menu, 'block' => $this]
        );

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getMobileHtml($this->_menu, $childrenWrapClass, $limit);

        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_after',
            ['menu' => $this->_menu, 'transportObject' => $transportObject]
        );
        $html = $transportObject->getHtml();

        return $html;
    }
	
	protected function _getMobileHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        $colBrakes = []
    ) {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        foreach ($children as $child) {
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            if (count($colBrakes) && $colBrakes[$counter]['colbrake']) {
                $html .= '</ul></li><li class="column"><ul>';
            }

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
            $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
                $child->getName()
            ) . '</span></a>' . $this->_addMobSubMenu(
                $child,
                $childLevel,
                $childrenWrapClass,
                $limit
            ) . '</li>';
            $itemPosition++;
            $counter++;
        }

        if (count($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }
	protected function _addMobSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        if (!$child->hasChildren()) {
            return $html;
        }

        $colStops = null;
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }

        $html .= '<ul class="level' . $childLevel . ' submenu">';
        $html .= $this->_getMobileHtml($child, $childrenWrapClass, $limit, $colStops);
        $html .= '</ul>';

        return $html;
    }
}