<?php
namespace Wanexo\Myfunction\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $date;
   
public function __construct(DateTime $date)
{    
  $this->dateFormat = $date;   
}
	public function specialPriceDiscount($_product){
    // {
		// $specialPrice = $_product->getSpecialPrice();
		// $specialFromDate = $_product->getSpecialFromDate();
		// $specialToDate = $_product->getSpecialToDate();
		// $currentDate = $this->dateFormat->gmtDate();
        // if($specialPrice){
			// if($specialFromDate && $specialToDate)
			// {
				// if($specialFromDate<=$currentDate && $specialToDate>=$currentDate)
				// {
					// $basePrice = $_product->getPrice();
					// $differencePrice = $basePrice - $specialPrice;
					// $percentage = ($differencePrice / $basePrice) * 100 ;
					// return '<span class="salePrice"> '.round($percentage).'%</span>';
				// }
			// }
			// else
			// {
            // $basePrice = $_product->getPrice();
            // $differencePrice = $basePrice - $specialPrice;
            // $percentage = ($differencePrice / $basePrice) * 100 ;
            // return '<span class="salePrice"> - '.round($percentage).'%</span>';
			// }
        // }
        // else
        // {
            // return;
        // }     
		$priceInfo=$_product->getPriceInfo();
			   if($priceInfo)
			{
		   $finalPrice=$priceInfo->getPrice('final_price')->getAmount()->getValue();
		   $basePrice=$priceInfo->getPrice('final_price')->getAmount()->getBaseAmount();
		   $regularPrice=$priceInfo->getPrice('regular_price')->getAmount()->getValue();
		   
		   
		   //$price=$_product->getPrice();
			 if($finalPrice && $regularPrice && $finalPrice < $regularPrice)
		   {
						  $differencePrice = $regularPrice - $finalPrice;
			  $percentage = ($differencePrice / $regularPrice) * 100 ;
			  return '<span class="salePrice"> '.round($percentage).'%</span>';
		   }
			}

    }
}