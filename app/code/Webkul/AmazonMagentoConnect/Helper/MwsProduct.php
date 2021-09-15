<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\AmazonMagentoConnect\Helper;

class MwsProduct extends \Magento\Framework\Model\AbstractModel
{
    public $sku;
    public $price;
    public $quantity = 0;
    public $productId;
    public $productIdType;
    public $conditionType = 'New';
    public $conditionNote;
    public $mageProductId;
    
    private $validationErrors = [];
    
    private $conditions = [
        'New', 'Refurbished', 'UsedLikeNew',
        'UsedVeryGood', 'UsedGood', 'UsedAcceptable'
    ];
    
    public function __construct(array $array = [])
    {
        foreach ($array as $property => $value) {
            $this->{$property} = $value;
        }
    }
    
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
    
    public function makeToArray()
    {
        return [
            'sku' => $this->sku,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'productId' => $this->productId,
            'productIdType' => $this->productIdType,
            'conditionType' => $this->conditionType,
            'conditionNote' => $this->conditionNote,
        ];
    }
    
    public function validate()
    {
        if (mb_strlen($this->sku) < 1 or strlen($this->sku) > 40) {
            $this->validationErrors['sku'] = 'Should be longer then 1 character and shorter then 40 characters';
        }
        
        $this->price = str_replace(',', '.', $this->price);
        
        $exploded_price = explode('.', $this->price);
        
        if (count($exploded_price) == 2) {
            if (mb_strlen($exploded_price[0]) > 18) {
                $this->validationErrors['price'] = 'Too high';
            } elseif (mb_strlen($exploded_price[1]) > 2) {
                $this->validationErrors['price'] = 'Too many decimals';
            }
        } else {
            $this->validationErrors['price'] = 'Looks wrong';
        }
        
        $this->quantity = (int) $this->quantity;
        $this->productId = (string) $this->productId;
        
        $productIdLength = mb_strlen($this->productId);
        
        switch ($this->productIdType) {
            case 'ASIN':
                if ($productIdLength != 10) {
                    $this->validationErrors['productId'] = 'ASIN should be 10 characters long';
                }
                break;
            case 'UPC':
                if ($productIdLength != 12) {
                    $this->validationErrors['productId'] = 'UPC should be 12 characters long';
                }
                break;
            case 'EAN':
                if ($productIdLength != 13) {
                    $this->validationErrors['productId'] = 'EAN should be 13 characters long';
                }
                break;
            default:
                $this->validationErrors['productIdType'] = 'Not one of: ASIN,UPC,EAN';
        }
        
        if (!in_array($this->conditionType, $this->conditions)) {
            $this->validationErrors['conditionType'] = 'Not one of: ' . implode($this->conditions, ',');
        }
        
        if ($this->conditionType != 'New') {
            $length = mb_strlen($this->conditionNote);
            if ($length < 1) {
                $this->validationErrors['conditionNote'] = 'Required if conditionType not is New';
            } elseif ($length > 1000) {
                $this->validationErrors['conditionNote'] = 'Should not exceed 1000 characters';
            }
        }
        
        if (count($this->validationErrors) > 0) {
            return false;
        } else {
            return true;
        }
    }
    
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}
