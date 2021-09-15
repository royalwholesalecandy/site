<?php 
/**
 * Akeans_ShowPriceAfterLogin extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Akeans
 * @package   Akeans_ShowPriceAfterLogin
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
?>
<?php
namespace Akeans\ShowPriceAfterLogin\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Categorylist implements ArrayInterface
{
   
    public function __construct(
		\Magento\Catalog\Helper\Category $category,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
    )
    {

		$this->category = $category;
        $this->categoryRepository = $categoryRepository;
    }
	
	
	/*
	* toOption Array
	*/
    public function toOptionArray()
    {
        $arr = $this->_toArray();
		
        $ret = [];

        foreach ($arr as $key => $value)
        {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
		
        return $ret;
    }
	/*
	* _toArray
	*/
    private function _toArray()
    {
      
		$categories = $this->category->getStoreCategories();
		$catagoryList = array();
        foreach($categories as $category) {
           // echo $category->getName().'<br/>';
            //$catagoryList[$category->getId()][] =  $category->getName();
            $catagoryList[$category->getId()] =  $category->getName();
            $categoryObj = $this->categoryRepository->get($category->getId());
            $subcategories = $categoryObj->getChildrenCategories();
            foreach($subcategories as $subcategorie) {
                //echo '    --> '.$subcategorie->getName().'<br/>';
				//$catagoryList[$category->getId()][$subcategorie->getId()] =  $subcategorie->getName();
				$catagoryList[$subcategorie->getId()] =  " ->".$subcategorie->getName();
            }
        }
		return $catagoryList;
    }
	
}
