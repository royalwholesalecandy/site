<?php
namespace Royal\Unishipper\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Simplexml\Element;
class Shipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
	\Magento\Shipping\Model\Carrier\CarrierInterface
{
	/**
	 * @var string
	 */
	protected $_code = 'smashingmagazine';

	/**
	 * @var \Magento\Shipping\Model\Rate\ResultFactory
	 */
	protected $_rateResultFactory;

	/**
	 * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
	 */
	protected $_rateMethodFactory;

	/**
	 * Shipping constructor.
	 *
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
	 * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
	 * @param \Psr\Log\LoggerInterface                                    $logger
	 * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
	 * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
	 * @param array                                                       $data
	 */
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
		\Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
		array $data = []
	) {
		$this->_rateResultFactory = $rateResultFactory;
		$this->_rateMethodFactory = $rateMethodFactory;
		parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
	}

	/**
	 * get allowed methods
	 * @return array
	 */
	public function getAllowedMethods()
	{
		return [$this->_code => $this->getConfigData('name')];
	}

	/**
	 * @return float
	 */
	private function getShippingPrice()
	{
		$configPrice = $this->getConfigData('price');

		$shippingPrice = $this->getFinalPriceWithHandlingFee($configPrice);

		return $shippingPrice;
	}

	/**
	 * @param RateRequest $request
	 * @return bool|Result
	 */
	public function collectRates(RateRequest $request)
	{
		if (!$this->getConfigFlag('active')) {
			return false;
		}

		/** @var \Magento\Shipping\Model\Rate\Result $result */
		$result = $this->_rateResultFactory->create();
		
		/** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
		$method = $this->_rateMethodFactory->create();
		$result = $this->getRateApi($result);
		/*$method->setCarrier($this->_code);
		$method->setCarrierTitle($this->getConfigData('title'));

		$method->setMethod($this->_code);
		$method->setMethodTitle($this->getConfigData('name'));

		$amount = $this->getShippingPrice();

		$method->setPrice($amount);
		$method->setCost($amount);

		$result->append($method);*/

		return $result;
	}
	public function getRateApi($result){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
		$state = $cart->getQuote()->getShippingAddress()->getRegionCode();
		$country = $cart->getQuote()->getShippingAddress()->getData('country_id');
		$city = $cart->getQuote()->getShippingAddress()->getCity();
		$zipcode = $cart->getQuote()->getShippingAddress()->getPostcode();


		$session = $cart;
		$weight = 0;
		//$qty = 0;
		$itemdetails = '';
		foreach ($session->getQuote()->getAllItems() as $item) {
			$weight += $item->getWeight() * $item->getQty();
			//$qty += $item->getQty();

			$itemdetails .= '<Item freightClass="70" sequence="1">
							<Weight units="lb">'.$item->getWeight().'</Weight>
							<Quantity units="Pallet">'.$item->getQty().'</Quantity>
						</Item>';
		}

		if($weight >= 4)
		{
			$date = strtotime(date('Y-m-d'));
			$pickupdate = date('m/d/Y', strtotime("+1 day", $date)). ' 15:00';
			$dropdate = date('m/d/Y', strtotime("+3 days", $date)). ' 15:00';
			$cartItemsCount = $session->getQuote()->getItemsCount();


			 $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
				<requests username="U18023811951" password="" id="47" token="e291953e-63d9-5783-4219-91872955d3f8">
					<request service="RateRequest">
						<RateRequest unitPricing="false">
							<uoneNumber>U18023811951</uoneNumber>
							<Constraints>
								<Contract/>
								<Carrier/>
								<Mode/>
								<ServiceFlags/>
							</Constraints>
							<HandlingUnits>
								<HandlingUnit stackable="false">
									<Quantity units="Pallet">1</Quantity>
									<Weight units="lb">'.$weight.'</Weight>
									<Items>
										'. $itemdetails.'
									</Items>
								</HandlingUnit>
							</HandlingUnits>
							<Events>
								<Event date="'.$pickupdate.'" type="Pickup" sequence="1">
									<Location>
										<City>Mt. Laurel</City>
										<State>NJ</State>
										<Zip>08054</Zip>
										<Country>US</Country>
									</Location>
								</Event>
								<Event date="'.$dropdate.'" type="Drop" sequence="2">
									<Location>
										<City>'.$city.'</City>
										<State>'.$state.'</State>
										<Zip>'.$zipcode.'</Zip>
										<Country>'.$country.'</Country>
									</Location>
								</Event>
							</Events>
						</RateRequest>
					</request>
				</requests>';


			//https://staging.sgiws.com/api/gateway.cfc
			/*Production
			https://prodws2.sgiws.com/api/gateway.cfc*/
			$URL = "https://prodws2.sgiws.com/api/gateway.cfc";

			//setting the curl parameters.
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,$URL);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

			if (curl_errno($ch))
			{
				// moving to display page to display curl errors
				  $curl_errno = curl_errno($ch) ;
				  $curl_error = curl_error($ch);
			}
			else {
				//getting response from server
				$response = curl_exec($ch);
				// print_r($response);
				curl_close($ch);
				/**
				 * 2019-12-14 Dmitry Fedyuk https://github.com/mage2pro
				 * «String could not be parsed as XML at app/code/Royal/Unishipper/Model/Carrier/Shipping.php:204»:
				 * https://github.com/royalwholesalecandy/core/issues/44
				 */
				try {$data = new \SimpleXMLElement($response);}
				catch (\Exception $e) {
					df_log_l($this, df_cc_n($e->getMessage(), $response), true);
					df_error($e);
				}
				if($data->response->RateResults->StatusCode == 0)
				{
					$PriceSheets = $data->response->RateResults->PriceSheets->PriceSheet;

					$count = count($PriceSheets);

					$dataArray = array();
					for($p=0; $p<$count; $p++)
					{
						$carrierTitle = (array) $PriceSheets[$p]->CarrierName;
						$price = (array) $PriceSheets[$p]->Total;
						$ContractId = (array) $PriceSheets[$p]->ContractId;
						$ServiceDays = (array) $PriceSheets[$p]->ServiceDays;
						$dataArray[$p]['price'] = $price[0];
						$dataArray[$p]['CarrierTitle'] = $carrierTitle[0];
						$dataArray[$p]['carrierSCAC'] = $ContractId[0];//'carrierSCAC'.$t;
						$dataArray[$p]['methodTitle'] = floor($ServiceDays[0]). ' Day '.$carrierTitle[0];

					}

					sort($dataArray);
					$codes = array();
					for($p=0; $p<count($dataArray); $p++)
					{
						$codes[] = $dataArray[$p]['CarrierTitle'];
						$rate = $this->_rateMethodFactory->create();
						$rate->setCarrier($this->_code); // $carrier[$p]
						$rate->setCarrierTitle($dataArray[$p]['CarrierTitle']); //$this->getConfigData('title')
						$rate->setMethod($dataArray[$p]['carrierSCAC']); // $carrier[$p]
						$rate->setMethodTitle($dataArray[$p]['methodTitle']); //
						$rate->setPrice($dataArray[$p]['price']); //$this->getConfigData('price')

						$rate->setCost(0);
						$result->append($rate);
						if($p == 2)
							break;
					}

				}
			}
		}
		
		return $result;
	}
}