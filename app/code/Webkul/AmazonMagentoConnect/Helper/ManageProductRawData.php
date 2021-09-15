<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\AmazonMagentoConnect\Helper;

use Webkul\AmazonMagentoConnect\Model\AmazonTempData;
use Webkul\AmazonMagentoConnect\Api\AmazonTempDataRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as AttrOptionCollectionFactory;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as ConfigurableAttributeModel;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;
use Magento\Catalog\Model\Product\AttributeSet\Options as AttributeSetList;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as AttrGroupCollection;
use Magento\Eav\Model\Entity as EavEntity;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Catalog\Model\Product\Option as ProductOptions;
use Webkul\AmazonMagentoConnect\Api\ProductMapRepositoryInterface;

class ManageProductRawData extends \Magento\Framework\App\Helper\AbstractHelper
{
	const SELECTED_PRICE_RULE = 'import';

	/*
	contain amazon client
	*/
	private $amzClient;

	/*
	contain attributre set id
	 */
	private $attributeSetId;

	/*
	contain default category id
	 */
	private $defaultCateId;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	private $storeManager;

	/**
	 * @var Data
	 */
	private $helper;

	/**
	 * @var \Webkul\AmazonMagentoConnect\Logger\Logger
	 */
	private $logger;

	/**
	 * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
	 */
	private $attrOptionCollectionFactory;

	/**
	 * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
	 */
	private $attributeModel;

	/**
	 * @var \Webkul\AmazonMagentoConnect\Model\Storage\DbStorage
	 */
	private $dbStorage;

	/**
	 *
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param Data $helper
	 * @param StoreManagerInterface $storeManager
	 * @param AmazonTempData $amazonTempData
	 * @param AmazonTempDataRepositoryInterface $amazonTempDataRepository
	 * @param AttributeFactory $attributeFactory
	 * @param \Webkul\AmazonMagentoConnect\Logger\Logger $logger
	 * @param AttrOptionCollectionFactory $attrOptionCollectionFactory
	 * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeModel
	 * @param ActionContext $actionContext
	 * @param \Webkul\AmazonMagentoConnect\Helper\Product $productHelper
	 * @param \Webkul\AmazonMagentoConnect\Model\ProductMap $productMap
	 * @param \Magento\Framework\Registry $registry
	 * @param AttributeSetList $attributeSetList
	 * @param AttrGroupCollection $attrGroupCollection
	 * @param ProductAttributeRepositoryInterface $productAttribute
	 * @param EavEntity $eavEntity
	 * @param AttributeManagementInterface $attributeManagement
	 * @param ProductOptions $productOptions
	 * @param ProductMapRepositoryInterface $productMapRepo
	 * @param \Webkul\AmazonMagentoConnect\Model\Storage\DbStorage $dbStorage
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		Data $helper,
		StoreManagerInterface $storeManager,
		AmazonTempData $amazonTempData,
		AmazonTempDataRepositoryInterface $amazonTempDataRepository,
		AttributeFactory $attributeFactory,
		\Webkul\AmazonMagentoConnect\Logger\Logger $logger,
		AttrOptionCollectionFactory $attrOptionCollectionFactory,
		\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeModel,
		ActionContext $actionContext,
		\Webkul\AmazonMagentoConnect\Helper\Product $productHelper,
		\Webkul\AmazonMagentoConnect\Model\ProductMap $productMap,
		\Magento\Framework\Registry $registry,
		AttributeSetList $attributeSetList,
		AttrGroupCollection $attrGroupCollection,
		ProductAttributeRepositoryInterface $productAttribute,
		EavEntity $eavEntity,
		AttributeManagementInterface $attributeManagement,
		ProductOptions $productOptions,
		ProductMapRepositoryInterface $productMapRepo,
		\Webkul\AmazonMagentoConnect\Model\Storage\DbStorage $dbStorage
	) {
		$this->storeManager = $storeManager;
		$this->helper = $helper;
		$this->amazonTempData = $amazonTempData;
		$this->attributeFactory = $attributeFactory;
		$this->amazonTempDataRepository = $amazonTempDataRepository;
		$this->logger = $logger;
		$this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
		$this->attributeModel = $attributeModel;
		$this->actionContext = $actionContext;
		$this->productHelper = $productHelper;
		$this->productMap = $productMap;
		$this->registry = $registry;
		$this->attributeSetList = $attributeSetList->toOptionArray();
		$this->attrGroupCollection = $attrGroupCollection;
		$this->productAttribute = $productAttribute;
		$this->attributeManagement = $attributeManagement;
		$this->entityTypeId = $eavEntity->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId();
		$this->entityType = \Magento\Catalog\Model\Product::ENTITY;
		$this->productOptions = $productOptions;
		$this->productMapRepo = $productMapRepo;
		$this->dbStorage = $dbStorage;
		parent::__construct($context);
	}

	/**
	 * get amazon client
	 *
	 * @return object
	 */
	public function getInitilizeAmazonClient()
	{
		if (!$this->amzClient) {
			$this->amzClient = $this->helper->getAmzClient();
		}
		return $this->amzClient;
	}

	/**
	 * get final report of amazon raw data
	 * @param  array $proReportGeneratedId
	 * @param  string $qtyGeneratedId
	 * @return array
	 */
	public function getFinalProductReport()
	{
		$this->amzClient = $this->getInitilizeAmazonClient();
		try {
			$items = [];
			if ($this->amzClient) {
				$config = $this->helper->config;
				$listReportId = $config['listing_report_id'];
				$inventoryReportId =  $config['inventory_report_id'];
				$listingReport = $this->amzClient->getReport($listReportId);

				if (is_array($listingReport)) {
					$inventoryReport = $this->amzClient->getReport($inventoryReportId);

					$mergedFinalReport = $this->mergeInventoryAndListingReport($listingReport, $inventoryReport);
					$finalReport = $mergedFinalReport['report'];
					if (!empty($finalReport)) {
						$items = $this->saveDataInWellFormat($finalReport);
					}
					$response = [
						'error' => 'false',
						'error_msg' => false,
						'data' => $items
					];
				} else {
					$response = [
						'error' => 'true',
						'error_msg' => __("Product report is not ready at Amazon Marketplace, Please try after some time.")
					];
				}
			} else {
				$response = [
					'error' => 'true',
					'error_msg' => __("Amazon Client Does not Initialize.")
				];
			}
		} catch (\Exception $e) {
			$this->logger->info('Helper ManageProductRawData getFinalProductReport : '.$e->getMessage());
			$response = [
				'error' => 'true',
				'error_msg' => __('Something went wrong, please check error log.'),
				'actual_error' => $e->getMessage()
			];
		}
		return $response;
	}

	/**
	 * merge inventory and listing report id
	 *
	 * @param array $listingReport
	 * @param array $inventoryReport
	 * @return array
	 */
	public function mergeInventoryAndListingReport($listingReport, $inventoryReport)
	{
		try {
			$finalReport = [];
			$newQtyReport = [];
			$productAsins = [];
			foreach ($listingReport as $proReportValue) {
				$productInventoryExist = false;
				if ($inventoryReport) {
					foreach ($inventoryReport as $qtyReportValue) {
						if ($proReportValue['asin1'] == $qtyReportValue['asin']) {
							foreach ($qtyReportValue as $qtyKey => $qtyValue) {
								$newQtyReport[trim($qtyKey)] = $qtyValue;
							}
							$productInventoryExist = true;
							$proReportValue['qty_avail'] = $newQtyReport['Quantity Available'];
							break;
						}
						if (!$productInventoryExist) {
							$proReportValue['qty_avail'] = $proReportValue['quantity'];
						}
					}
				} else {
					$proReportValue['qty_avail'] = $proReportValue['quantity'];
				}
				$productAsins[] = $proReportValue['asin1'];
				$finalReport[] = $proReportValue;
			}
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData mergeInventoryAndListingReport : '.$e->getMessage());
		}
		return ['report'=>$finalReport,'asins'=>$productAsins];
	}

	/**
	 * arrange data in well format
	 * @param  array $wholeRawData
	 * @return array
	 */
	public function saveDataInWellFormat($wholeRawData)
	{

		$completeWellFormedData = [];
		$items = [];
		$defaultQty = $this->helper->getDefaultProductQty();
		$defaultWeight = $this->helper->getDefaultProductWeight();
		$this->defaultCateId = $this->helper->getDefaultCategory();
		$baseCurrency = $this->storeManager->getStore()->getBaseCurrencyCode();
		$amzCurrency = $this->helper->getAmazonCurrencyCode();
		$this->attributeSetId = $this->helper->getAttributeSet();
		$dt = new \DateTime();
		$currentDate = $dt->format('Y-m-d\TH:i:s');

		$tempAvlImported = $this->amazonTempData->getCollection()                                                                ->addFieldToFilter('item_type', 'product')
							->getColumnValues('item_id');

		$alreadyMapped = $this->productMap->getCollection()->getColumnValues('amazon_pro_id');

		$tempAvlImported =  array_merge($tempAvlImported, $alreadyMapped);

		try {
			foreach ($wholeRawData as $amzProData) {
				if (in_array($amzProData['asin1'], $tempAvlImported)) {
					continue;
				}
				$wholeData = [
					'type_id' => 'simple',
					'status' => 1,
					'attribute_set_id' => $this->attributeSetId,
					'producttypecustom' => 'customproductsimple',
					'category' => [$this->defaultCateId],
					'name' => utf8_encode($amzProData['item-name']),
					'description' => utf8_encode($amzProData['item-description']),
					'short_description' => ' ',
					'sku' => $amzProData['seller-sku'],
					'price' => $this->convertPriceToBase($amzProData['price']),
					'currency_id' =>  $this->helper->getAmazonCurrencyCode(),
					'stock' => empty($amzProData['qty_avail'])? $defaultQty :$amzProData['qty_avail'],
					'is_in_stock' => 1,
					'tax_class_id' => 0,
					'weight' => $defaultWeight,
					'asin' => $amzProData['asin1'],
					'wk_fulfillment_channel' => $amzProData['fulfillment-channel']
				];
				$completeWellFormedData[] = [
					'item_type' => 'product',
					'item_id' => $amzProData['asin1'],
					'item_data' => json_encode($wholeData),
					'created_at' => $currentDate,
					'mage_amz_account_id' => $this->helper->accountId,
					'amz_product_id' => $amzProData['product-id'],
					'product_sku'    => $amzProData['seller-sku']
				];
				$items[] = $amzProData['asin1'];
			}
			$this->InsertDataInBulk($completeWellFormedData);
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData saveDataInWellFormat : '.$e->getMessage());
		}

		return $items;
	}

	/**
	 * save data in table
	 * @param  array $completeWellFormedData
	 * @return null
	 */
	public function InsertDataInBulk($completeWellFormedData = [])
	{
		try {
			if (!empty($completeWellFormedData)) {
				$numberOfRecond = 500;
				$indexNumber = 0;
				$allCount = count($completeWellFormedData);
				if (count($completeWellFormedData) > $numberOfRecond) {
					while (count($completeWellFormedData) > $indexNumber) {
						$slicedArray = [];
						if (count($completeWellFormedData) > ($indexNumber+$numberOfRecond)) {
							$slicedArray = array_slice($completeWellFormedData, $indexNumber, $numberOfRecond);

							$this->dbStorage->insertMultiple('wk_amazon_tempdata', $slicedArray);
							$indexNumber = $indexNumber + $numberOfRecond;
						} else {
							$remainingIndexes = $allCount -  $indexNumber;
							$slicedArray = array_slice($completeWellFormedData, $indexNumber, $remainingIndexes);
							$this->dbStorage->insertMultiple('wk_amazon_tempdata', $slicedArray);
							$indexNumber = $indexNumber + $remainingIndexes;
							break;
						}
					}
				} else {
					$this->dbStorage->insertMultiple('wk_amazon_tempdata', $completeWellFormedData);
				}
			}
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData InsertDataInBulk : '.$e->getMessage());
		}
	}

	/**
	 * get details information about product
	 * @param  array $amzProData
	 * @return array
	 */
	private function getDetailOfAmazonProduct($amzProData)
	{
		$items = [];
		$wholeData=[];
		try {
			foreach ($amzProData as $key => $value) {
				$tempAvl = $this->amazonTempDataRepository
							->getCollectionByItemId(
								'product',
								$value['asin1']
							)
							->getFirstItem();
				if ($tempAvl->getEntityId()) {
					if (!in_array($tempAvl->getEntityId(), $items)) {
						array_push($items, $tempAvl->getEntityId());
					}
					continue;
				}
				$isAlreadyMapped = $this->productMapRepo->getCollectionByAmzProductId($value['asin1']);

				if ($isAlreadyMapped->getSize()) {
					continue;
				}
				$wholeData = $this->getProductByAsin($value['asin1'], $value, false);

				$dt = new \DateTime();
				$currentDate = $dt->format('Y-m-d\TH:i:s');

				$tempdata = [
							'item_type' => 'product',
							'item_id' => ($wholeData['type_id'] === 'configurable')?$wholeData['parent_asin'] : $value['asin1'],
							'item_data' => json_encode($wholeData),
							'created_at' => $currentDate,
							'mage_amz_account_id' => $this->helper->accountId
						];
				$temppro = $this->amazonTempData;
				$temppro->setData($tempdata);
				$item = $temppro->save();
				if (!in_array($item->getEntityId(), $items)) {
					array_push($items, $item->getEntityId());
				}
			}
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData getDetailOfAmazonProduct : '.$e->getMessage());
		}

		return $items;
	}

	/**
	 * get details about item asin
	 * @param  string  $asin
	 * @param  boolean $value
	 * @param  boolean $viaCron
	 * @return array|string
	 */
	public function getProductByAsin($asin, $value = false, $viaCron = false, $isSimple = false)
	{
		// $hasVariation = $this->amzProHasVariation($asin);
		// if ($hasVariation) {
		//     $wholeData = $hasVariation;
		// } else {
			$baseCurrency = $this->storeManager->getStore()->getBaseCurrencyCode();
			$amzCurrency = $this->helper->getAmazonCurrencyCode();
			$cmptProData  = $this->amzClient->getMatchingProduct([$asin]);
			$defaultQty = $this->helper->getDefaultProductQty();
			$qty = null;
		if ($cmptProData['GetMatchingProductResult']['@attributes']['status'] !== 'Success') {
			return false;
		}
			$cmptPriceProData = $this->amzClient->getCompetitivePricingForASIN([$asin]);
			$getMyPrice = $this->amzClient->getMyPriceForASIN([$asin]);

			$price = $this->getAmzProductPrice($getMyPrice, $cmptPriceProData);

			$amzProduct = $cmptProData['GetMatchingProductResult']['Product'];

			$name = $amzProduct['AttributeSets']['ItemAttributes']['Title'];
			$description = $amzProduct['AttributeSets']['ItemAttributes']['Title'];
		if (isset($getMyPrice['GetMyPriceForASINResult']['Product']['Offers']) && isset($getMyPrice['GetMyPriceForASINResult']['Product']['Offers']['Offer']) && isset($getMyPrice['GetMyPriceForASINResult']['Product']['Offers']['Offer']['SellerSKU'])) {
			$sku = $getMyPrice['GetMyPriceForASINResult']['Product']['Offers']['Offer']['SellerSKU'];
		} else {
			$sku = $amzProduct['Identifiers']['MarketplaceASIN']['ASIN'];
		}
			$defaultWeight = $this->helper->getDefaultProductWeight();
			$this->attributeSetId = $this->helper->getAttributeSet();
			$this->defaultCateId = $this->helper->getDefaultCategory();
		if (isset($cmptProData['GetMatchingProductResult']['Product']['AttributeSets'])) {
			foreach ($cmptProData['GetMatchingProductResult']['Product']['AttributeSets'] as $proAttribute) {
				if (isset($proAttribute['PackageDimensions']['Weight'])) {
					$weight = $proAttribute['PackageDimensions']['Weight'];
					break;
				}
			}
		}
			/**For without variation product**/
			$wholeData = ['type_id' => 'simple',
				'status' => 1,
				'attribute_set_id' => $this->attributeSetId,
				'producttypecustom' => 'customproductsimple',
				'category' => [$this->defaultCateId],
				'name' => $name,
				'description' => $description,
				'short_description' => ' ',//$data['Description'],
				'sku' => $sku,
				'price' => $this->convertPriceToBase($price),
				'currency_id' =>  $this->helper->getAmazonCurrencyCode(),
				'stock' => empty($qty)? $defaultQty :$qty,
				'is_in_stock' => 1,
				'tax_class_id' => 0,
				'weight' => empty($weight)?$defaultWeight:$weight,
				'asin' => $asin
			];

		// }
			if (!$viaCron) {
				return $wholeData;
			} else {
				$tempProData = $wholeData;
				$request = $this->actionContext->getRequest();
				foreach ($tempProData as $key => $value) {
					$request->setParam($key, $value);
				}
				$result = $this->productHelper
						->saveSimpleProduct($request);
				$data = [
					'amazon_pro_id' => $asin,
					'name' => $tempProData['name'],
					'product_type' => $tempProData['type_id'],
					'mage_amz_account_id'   => $this->helper->accountId,
					'amz_product_id'    => ''
				  ];

				$id = false;
				if (isset($result['product_id']) && $result['product_id']) {
					$data['magento_pro_id'] = $result['product_id'];
					$data['mage_cat_id'] = $tempProData['category'][0];

					$record = $this->productMap;
					$savedRecord = $record->setData($data)->save();

					$this->registry->unregister('product');
					$this->registry->unregister('current_product');
					$this->registry->unregister('current_store');

					$id = $savedRecord->getId();
				} else {
					$result['error'] = 1;
					$result['sku'] = $result['sku'];
				}
				return $result;
			}
	}

	/**
	 * check amazon product has variation or not
	 *
	 * @param int $proAsin
	 * @return array|| null
	 */
	public function amzProHasVariation($proAsin, $tempProData = false)
	{
		try {
			$this->getInitilizeAmazonClient();
			$cmptProData  = $this->amzClient->getMatchingProduct([$proAsin]);

			if (empty($cmptProData['GetMatchingProductResult']['Product']['Relationships'])) {
				$productAttributes = $this->getAdditionAttributes($cmptProData);
				$tempProData['addition_attrs'] = $productAttributes;
				return ['is_simple' => true, 'data' => $tempProData];
			} else {
				$productWithWholeVariation = $this->getRawDataWithVariation($cmptProData);
				if (isset($productWithWholeVariation['is_simple']) && $productWithWholeVariation['is_simple']) {
					$tempProData['description'] = isset($productWithWholeVariation['description']) ? $productWithWholeVariation['description'] : $tempProData['description'];
					return ['is_simple' => true, 'data' => $tempProData];
				} else {
					return ['is_simple' => false, 'data' => $productWithWholeVariation];
				}
			}
		} catch (\Exception $e) {
			$this->logger->info('Helper ManageProductRawData amzProHasVariation : '.$e->getMessage());
		}
	}

	/**
	 * get product feature description of product
	 *
	 * @param array $cmptProData
	 * @return void
	 */
	public function getAdditionAttributes($cmptProData)
	{
		$mappedAttrs = $this->helper->getMappedAttributeData();
		$attrs = [];
		if (isset($cmptProData['GetMatchingProductResult']) && isset($cmptProData['GetMatchingProductResult']['Product']['AttributeSets']['ItemAttributes'])) {
			$amzAttributes = $cmptProData['GetMatchingProductResult']['Product']['AttributeSets']['ItemAttributes'];
			foreach ($mappedAttrs as $amzCode => $mageCode) {
				if (isset($amzAttributes[$amzCode])) {
					$attrs[$mageCode] = $amzAttributes[$amzCode];
				}
			}
		}
		return $attrs;
	}

	/**
	 * get product attribute with variation
	 *
	 * @param array $cmptProData
	 * @return void
	 */
	public function getRawDataWithVariation($cmptProData)
	{
		try {
			$parentAsinId = null;
			$defaultWeight = $this->helper->getDefaultProductWeight();
			$this->attributeSetId = $this->helper->getAttributeSet();
			$this->defaultCateId = $this->helper->getDefaultCategory();
			if (isset($cmptProData['GetMatchingProductResult']['Product']['Relationships']['VariationParent'])) {
				$parentAsinId = $this->checkParentAsinValue($cmptProData['GetMatchingProductResult']['Product']['Relationships']['VariationParent']);
			} else {
				$parentAsinId = $cmptProData['GetMatchingProductResult']['Product']['Identifiers']['MarketplaceASIN']['ASIN'];
			}

			$parentAsinData  = $this->amzClient
					->getMatchingProduct([$parentAsinId]);
			if (isset($parentAsinData['GetMatchingProductResult']['Product']['Relationships']['VariationChild']) && $parentAsinData['GetMatchingProductResult']['Product']['Relationships']['VariationChild']) {
				foreach ($parentAsinData as $key => $parentVal) {
					$wholeData = $this->getConfigProductData($parentVal['Product']['AttributeSets'], $parentAsinId);
					$quictProData = [];
					$noMoreAsin = false;

					$variationChild = isset($parentVal['Product']['Relationships']['VariationChild'][0]) ?
									$parentVal['Product']['Relationships']['VariationChild'] :
									[0 => $parentVal['Product']['Relationships']['VariationChild']];
					$variationData = [];
					$variatonAsin = [];
					foreach ($variationChild as $attributes => $attrValue) {
						$childAsin = '';
						if (isset($attrValue['Identifiers'])) {
							if (count($attrValue) > 1) {
								$childAsin = $attrValue['Identifiers']['MarketplaceASIN']['ASIN'];
								$variationData[$childAsin] = $attrValue;
								$variatonAsin[] = $childAsin;
							}
						}
					}
					$quickProData = $this->manageVariationData($variationData, $variatonAsin);
					break;
				}
			}
			if (isset($quickProData['quickProCompltData']) && empty($quickProData['quickProCompltData'])) {
				$wholeData['is_simple'] = true;
				return $wholeData;
			} else {
				$wholeData['supperattr'] = array_keys($quickProData['superAttributes']);
				$wholeData['assocate_pro'] = $quickProData['quickProCompltData'];
			}

			return $wholeData;
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData getRawDataWithVariation : '.$e->getMessage());
			return false;
		}
	}

	/**
	 * manage variation data
	 *
	 * @param array $variationData
	 * @param array $variatonAsin
	 * @return void
	 */
	public function manageVariationData($variationData, $variatonAsin)
	{
		if(empty($variatonAsin)) {
			return [
				'superAttributes' => [],
				'quickProCompltData'    => []
			];
		}
		$quickProCompltData = [];$superAttr = [];
		$collection = $this->amazonTempData->getCollection()
							->addFieldToFilter('item_type', 'product')
							->addFieldToFilter('item_id', ['in' => $variatonAsin]);
		foreach($collection as $tempRecord) {
			$asin = $tempRecord->getItemId();
			$tempProData = json_decode($tempRecord->getItemData(), true);
			if(!empty($tempProData)) {
				$superAttribute = $this->createSuperAttrMagento($variationData[$asin], $this->attributeSetId);
				if(empty($superAttr)) {
					$superAttr = $superAttribute;
				}
				$quickProData = $this->getQuickCmptProData($asin, $tempProData, $superAttribute);
				if(!empty($quickProData)) {
					$quickProCompltData[] = $quickProData;
				}
			}
		}
		return [
			'superAttributes' => $superAttr,
			'quickProCompltData'    => $quickProCompltData
		];
	}

	/**
	 * get associate product data
	 *
	 * @param int $asin
	 * @param array $superAttribute
	 * @return void
	 */
	public function getQuickCmptProData($asin, $quickProData, $superAttribute)
	{
		$amzAssociateData = $this->amzClient->getMatchingProduct([$asin]);
		if (!isset($amzAssociateData['GetMatchingProductResult'])) {
			return false;
		}
		if (!empty($quickProData)) {
			$quickProData['visibility'] = 1;
			$quickProData['qty'] = $quickProData['stock'];
			$neededIndex = ['status','sku','price','currency_id','qty','is_in_stock','tax_class_id','weight','visibility','asin','name','description','category'];
			foreach ($quickProData as $key => $proData) {
				if (!in_array($key, $neededIndex)) {
					unset($quickProData[$key]);
				}
			}

			foreach ($amzAssociateData['GetMatchingProductResult']['Product']['AttributeSets'] as $proAttribute) {
				if (isset($superAttribute)) {
					foreach ($superAttribute as $attrCode => $attrValue) {
							$amzAttr = explode('_', $attrCode);
							$actualAmzAttr = $amzAttr[1];

							$proAttribute = array_change_key_case($proAttribute, CASE_LOWER);
						if (isset($proAttribute[$actualAmzAttr])) {
							$attributeData[$attrCode] = $attrValue;
							$attributeInfo = $this->attributeModel->create()
								->getCollection()
								->addFieldToFilter(
									'attribute_code',
									$attrCode
								)
								->getFirstItem();
							$attribute = $this->attributeModel
										->create()
										->load(
											$attributeInfo->getAttributeId()
										);
							$quickProData[$attrCode] = $attribute->getSource()->getOptionId($proAttribute[$actualAmzAttr]);
						}
					}break;
				}
			}
		}
		return $quickProData;
	}

	/**
	 * get product data by product api
	 *
	 * @param int $asin
	 * @param array $amzAssociateData
	 * @return array
	 */
	public function getFormattedDataByProApi($asin, $amzAssociateData)
	{
		try {
			$getMyPriceAssociate = $this->amzClient->getMyPriceForASIN([$asin]);

			$competitivePriceAssociate = $this->amzClient->getCompetitivePricingForASIN([$asin]);

			$price = $this->getAmzProductPrice($getMyPriceAssociate, $competitivePriceAssociate);

			$amzAssociateProduct = $amzAssociateData['GetMatchingProductResult']['Product'];

			$name = $amzAssociateProduct['AttributeSets']['ItemAttributes']['Title'];
			$description = $amzAssociateProduct['AttributeSets']['ItemAttributes']['Title'];

			if (isset($getMyPriceAssociate['GetMyPriceForASINResult']['Product']['Offers']) && isset($getMyPriceAssociate['GetMyPriceForASINResult']['Product']['Offers']['Offer']) && isset($getMyPriceAssociate['GetMyPriceForASINResult']['Product']['Offers']['Offer']['SellerSKU'])) {
				$sku = $getMyPriceAssociate['GetMyPriceForASINResult']['Product']['Offers']['Offer']['SellerSKU'];
			} else {
				$sku = $getMyPriceAssociate['GetMyPriceForASINResult']['Product']['Identifiers']['MarketplaceASIN']['ASIN'];
			}
			$defaultQty = $this->helper->getDefaultProductQty();

			$quictProData = [
				'status' => 1,
				'sku' => $sku,
				'price' => $this->convertPriceToBase($price),
				'currency_id' => $this->helper->getAmazonCurrencyCode(),
				'qty' => !isset($qty) && empty($qty)? $defaultQty :$qty,
				'is_in_stock' => 1,
				'tax_class_id' => 0,
				'weight' => '1',
				'visibility' => 1,
				'asin' => $asin
			];

			return $quictProData;
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData getFormattedDataByProApi : '.$e->getMessage());
		}
	}

	/**
	 * get product data from product report
	 *
	 * @param int $asin
	 * @return array
	 */
	public function getDataFromReportByAsin($asin)
	{
		$formattedProductData = [];
		try {
			$tmpDataByReport = $this->amazonTempDataRepository
								->getCollectionByItemId(
									'product',
									$asin
								)
								->getFirstItem();
			if ($tmpDataByReport->getEntityId()) {
				$tempProData = json_decode($tmpDataByReport->getItemData(), true);
				if (!empty($tempProData)) {
					return $tempProData;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData getDataFromReportByAsin : '.$e->getMessage());
		}
	}

	/**
	 * convert amazon price to base currency
	 *
	 * @param integer $price
	 * @return int
	 */
	public function convertPriceToBase($price)
	{
		try {
			$ruleAppliedPrice = '';
			if ($ruleData = $this->helper->getPriceRuleByPrice($price)) {
				$ruleAppliedPrice = $this->helper->getPriceAfterAppliedRule($ruleData, $price, self::SELECTED_PRICE_RULE);
			}
			$price = empty($ruleAppliedPrice) ? $price : $ruleAppliedPrice;
			$baseCurrency = $this->storeManager->getStore()->getBaseCurrencyCode();
			$amzCurrency = $this->helper->getAmazonCurrencyCode();
			if ($amzCurrency !== $baseCurrency) {
				$currencyRate = $this->helper->getCurrencyRate($amzCurrency);
				if (!empty($currencyRate)) {
					$price = $price/$currencyRate;
				}
			}
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData convertPriceToBase : '.$e->getMessage());
		}
		return round($price, 2);
	}

	/**
	 * get amzon product price
	 * @param  array $getMyPrice
	 * @param  array $getCompetitivePricing
	 * @return int
	 */
	public function getAmzProductPrice($getMyPrice, $getCompetitivePricing)
	{
		$price = null;
		/**
		 * 2019-12-26 Dmitry Fedyuk https://github.com/mage2pro
		 * «Undefined index: GetCompetitivePricingForASINResult
		 * in app/code/Webkul/AmazonMagentoConnect/Helper/ManageProductRawData.php on line 843»:
		 * https://github.com/royalwholesalecandy/core/issues/74
		 */
		if (isset($getCompetitivePricing['GetCompetitivePricingForASINResult'])) {
			try {
				$competitivePrices = $getCompetitivePricing['GetCompetitivePricingForASINResult']['Product']['CompetitivePricing']['CompetitivePrices'];
				if (count($competitivePrices)) {
					if (isset($competitivePrices['CompetitivePrice']) && isset($competitivePrices['CompetitivePrice']['Price']) && isset($competitivePrices['CompetitivePrice']['Price']['ListingPrice']) && isset($competitivePrices['CompetitivePrice']['Price']['ListingPrice']['Amount'])) {
							$price = $competitivePrices['CompetitivePrice']['Price']['ListingPrice']['Amount'];
					}
				} elseif (empty($price)) {
					$getOfferPrice = $getMyPrice['GetMyPriceForASINResult']['Product'];
					if (isset($getOfferPrice['Offers']['Offer']) && isset($getOfferPrice['Offers']['Offer']['RegularPrice'])) {
						$price = $getOfferPrice['Offers']['Offer']['RegularPrice']['Amount'];
					}
				}

				if (empty($price)) {
					$price = '1000';
				}
			} catch (\Exception $e) {
				$this->logger->info('ManageProductRawData getAmzProductPrice : '.$e->getMessage());
			}
		}
		return $price;
	}

	/**
	 * get configurable product data
	 * @param  array $configAttribute
	 * @param  string $parentAsinId
	 * @return array
	 */
	public function getConfigProductData(
		$configAttribute,
		$parentAsinId
	) {
		try {
			foreach ($configAttribute as $key => $attribute) {
				$proFeaturesString = '';
				$description = $attribute['Title'];
				$wholedata = [
					'type_id' => 'configurable',
					'status' => 1,
					'attribute_set_id' => $this->attributeSetId,
					'producttypecustom' => 'customproductsimple',
					'category' => [$this->defaultCateId],
					'name' => $attribute['Title'],
					'description' => $description,
					'short_description' => ' ',//$data['Description'],
					'sku' => $parentAsinId,
					'price' => '0',
					'currency_id' => $this->helper->getAmazonCurrencyCode(),
					'stock' => '0',
					'is_in_stock' => 1,
					'tax_class_id' => 0,
					'weight' => '1',
					'asin' => $parentAsinId,
					'is_simple' => false
				];
			}
			return $wholedata;
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData getConfigProductData : '.$e->getMessage());
		}
	}

	/**
	 * get parent asin data
	 * @param  string $parentAsinId
	 * @param  string $productAsin
	 * @return array
	 */
	public function getParentAsinData($parentAsinId, $productAsin)
	{
		try {
			$parentAsinData  = $this->amzClient
						->getMatchingProduct([$parentAsinId]);
			$attributes = [];
			if (isset($parentAsinData['GetMatchingProductResult']['Product']['Relationships']['VariationChild']) && $parentAsinData['GetMatchingProductResult']['Product']['Relationships']['VariationChild']) {
				foreach ($parentAsinData['GetMatchingProductResult']['Product']['Relationships'] as $parentVal) {
					if (isset($parentVal['Identifiers']['MarketplaceASIN']['ASIN']) && ($parentVal['Identifiers']['MarketplaceASIN']['ASIN'] == $productAsin)) {
						foreach ($parentVal as $productKey => $productAttribute) {
							if ($productKey != 'Identifiers') {
								$attributes[$productKey] = $productAttribute;
							}
						}
						break;
					}
				}
			}
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData getParentAsinData : '.$e->getMessage());
		}
		return $parentVal;
	}

	/**
	 * check product parent asin value
	 * @param  array $variations
	 * @return int|bool
	 */
	private function checkParentAsinValue($variations)
	{
		$parentAsin = null;
		try {
			foreach ($variations as $value) {
				if (isset($value['MarketplaceASIN']['ASIN'])) {
					$parentAsin = $value['MarketplaceASIN']['ASIN'];
					break;
				}
			}
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData checkParentAsinValue : '.$e->getMessage());
		}
		return $parentAsin;
	}

	/**
	 * getAttributeInfo
	 * @param string $mageAttrCode
	 * @return false | Magento\Catalog\Model\ResourceModel\Eav\Attribute
	 */
	private function _getAttributeInfo($mageAttrCode)
	{
		$attributeInfo = false;
		try {
			$attributeInfoColl = $this->attributeFactory->create()
										->getCollection()
										->addFieldToFilter(
											'attribute_code',
											['eq' => $mageAttrCode]
										);
			if ($attributeInfoColl->getSize()) {
				foreach ($attributeInfoColl as $attrInfoData) {
					$attributeInfo = $attrInfoData;
				}
			}
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData _getAttributeInfo : '.$e->getMessage());
		}
		return $attributeInfo;
	}

	/**
	 * createSuperAttrMagento return supper attributes code with values
	 * @param object $variations
	 * @return array
	 */
	public function createSuperAttrMagento($mageSupAttrs, $attributeSetId)
	{
		$mapAttr = [];
		$option = [];
		$supperAttributes = [];
		try {
			$mageSupAttrs = $this->_getSupAttrWithValue($mageSupAttrs);
			$allStores = $this->storeManager->getStores();
			foreach ($mageSupAttrs as $attrCode => $values) {
				$i = 0;
				if ($attrCode == '') {
					continue;
				}
				$attributeCode = str_replace(' ', '_', $attrCode);
				// $attributeCode = str_replace('ns2', '', $attributeCode);
				$attributeCode = preg_replace('/[^A-Za-z0-9\_]/', '', $attributeCode);
				$mageAttrCode = substr('config_'.strtolower($attributeCode), 0, 30);
				if ($mageAttrCode == 'config_') {
					continue;
				}
				// $attrCode = str_replace('ns2', '', $attrCode);
				$attributeInfo = $this->_getAttributeInfo($mageAttrCode);
				$supperAttributes[$mageAttrCode] = $values;

				$mapAttr[$attrCode] = $mageAttrCode;
				$attributeGroupId = $this->getAttributeGroupId('Amazon Product Variation', $attributeSetId);
				if ($attributeInfo === false) {
					$attributeScope = \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL;
					$attrData = [
						'entity_type_id' => $this->entityTypeId,
						'attribute_code' => $mageAttrCode,
						'frontend_label' => [0 => $attrCode],
						'attribute_group_id' => $attributeGroupId,
						'attribute_set_id' => $attributeSetId,
						'backend_type' => 'int',
						'frontend_input' => 'select',
						'global' => $attributeScope,
						'visible' => true,
						'required' => false,
						'is_user_defined' => true,
						'searchable' => false,
						'filterable' => false,
						'comparable' => false,
						'visible_on_front' => true,
						'visible_in_advanced_search' => false,
						'unique' => false,
					];

					$labels = [];
					$labels[0] = $attrCode;
					foreach ($allStores as $store) {
						$labels[$store->getId()] = $attrCode;
					}
					$option = $this->_getAttributeOptions($mageAttrCode, $labels, $values);
					try {
						$attrData['option'] = $option;
						$attribute = $this->attributeFactory->create();
						$attribute->setData($attrData);
						$this->saveObject($attribute);
					} catch (\Exception $e) {
						$this->logger->info($e->getMessage());
					}
				} else {
					try {
						/****For get Attribute Options ****/
						$option = $this->_getAttributeOptionsForEdit($attributeInfo->getAttributeId(), $values);

						$this->attributeManagement->assign(
							$this->entityType,
							$attributeSetId,
							$attributeGroupId,
							$mageAttrCode,
							$attributeInfo->getAttributeId()
						);
						if (isset($option['value'])) {
							$attr = $this->productAttribute->get($attributeInfo->getAttributeCode());
							$attr->setOption($option);
							$this->productAttribute->save($attr);
						}
					} catch (\Exception $e) {
						$this->logger->info('Create createSuperAttrMagento : '.$e->getMessage());
					}
					$option = [];
				}
			}
		} catch (\Exception $e) {
			$this->logger->info('Create createSuperAttrMagento : '.$e->getMessage());
			$supperAttributes = [];
		}
		return $supperAttributes;
	}

	/**
	 * saveObject
	 *
	 */
	private function saveObject($object)
	{
		$object->save();
	}

	/**
	 * getAttributeGroupId
	 * @param $groupName
	 */
	private function getAttributeGroupId($groupName, $attributeSetId)
	{
		try {
			$group = $this->attrGroupCollection->create()
										->addFieldToFilter('attribute_group_name', $groupName)
										->addFieldToFilter('attribute_set_id', $attributeSetId)
										->setPageSize(1)->getFirstItem();
			if (!$group->getAttributeGroupId()) {
				$data = [
					'attribute_group_name' => $groupName,
					'attribute_set_id' => $attributeSetId,
					'attribute_group_code' => md5($groupName)
				];
				$group = $group->setData($data)->save();
			}
			return $group->getId();
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData InsertDataInBulk : '.$e->getMessage());
		}
	}

	/**
	 * _getAttributeOptionsForEdit
	 * @param $mageAttrId string
	 * @param $values array of spicification/variations options
	 * @return array of prepared all options of attribute
	 */
	private function _getAttributeOptionsForEdit($mageAttrId, $values)
	{
		try {
			$attributeOptions = $this->attrOptionCollectionFactory->create()
												->setPositionOrder('asc')
												->setAttributeFilter($mageAttrId)
												->setStoreFilter(0)->load();
			$optionsValues = [];
			foreach ($attributeOptions as $kay => $attributeOption) {
				array_push($optionsValues, strtolower($attributeOption->getDefaultValue()));
			}
			$allStores = $this->storeManager->getStores();
			$option = [];
			$option['attribute_id'] = $mageAttrId;
			if (in_array(strtolower($values), $optionsValues) === false && $values != '' && $values != ' ') {
				$option['value']['wk'.$values][0] = $values;
				foreach ($allStores as $store) {
					$option['value']['wk'.$values][$store->getId()] = $values;
				}
			}
			return $option;
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData _getAttributeOptionsForEdit : '.$e->getMessage());
		}
	}

	/**
	 * getAttributeOprionsByAttrId
	 * @param int $attributeId
	 * @return AttrOptionCollectionFactory
	 */
	private function _getAttributeOprionsByAttrId($attributeId)
	{
		try {
			$attrOptions = $this->attrOptionCollectionFactory
											->create()
											->setPositionOrder('asc')
											->setAttributeFilter($attributeId)
											->setStoreFilter(0)
											->load();
			return $attrOptions;
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData _getAttributeOprionsByAttrId : '.$e->getMessage());
		}
	}

	/**
	 * getMageSupperAttribute for get Attribute label with its value
	 * @param array $attrVals
	 * @param array
	 */
	private function _getSupAttrWithValue($attrVals)
	{
		$allAttributes =[];
		try {
			$attrVals = isset($attrVals[0]) ? $attrVals : [$attrVals];

			foreach ($attrVals as $productKey => $productAttribute) {
				foreach ($productAttribute as $key => $value) {
					if ($key != 'Identifiers') {
						$mageSupAttrs[$key] = $value;
					}
				}
			}
			return $mageSupAttrs;
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData _getSupAttrWithValue : '.$e->getMessage());
		}
	}

	/**
	 * @param $mageAttrCode string
	 * @param $labels array of options label according to store
	 * @param $values array of spicification/variations options
	 * @return array of prepared all options of attribute
	 */
	private function _getAttributeOptions($mageAttrCode, $labels, $values)
	{
		try {
			$allStores = $this->storeManager->getStores();
			$attributeInfo = $this->_getAttributeInfo($mageAttrCode);
			$option = [];
			if ($attributeInfo) {
				$attribute = $this->attributeFactory->create()->load($attributeInfo->getAttributeId());
				$attribute->setStoreLabels($labels)->save();
				$option['attribute_id'] = $attribute->getAttributeId();
				if (!is_array($values)) {
					$option['value']['wk'.$values][0] = $values;
					foreach ($allStores as $store) {
						$option['value']['wk'.$values][$store->getId()] = $values;
					}
				} else {
					foreach ($values as $key => $value) {
						$option['value']['wk'.$value][0] = $value;
						foreach ($allStores as $store) {
							$option['value']['wk'.$value][$store->getId()] = $value;
						}
					}
				}
			}
			return $option;
		} catch (\Exception $e) {
			$this->logger->info('ManageProductRawData _getAttributeOptions : '.$e->getMessage());
		}
	}
}
