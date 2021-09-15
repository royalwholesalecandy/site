<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PENDING_REGISTRATION
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PendingRegistration\Observers\Save;

class SaveCustomer extends \Itoris\PendingRegistration\Observers\Observer
{
	public $emailSendFlag = false;

	function execute(\Magento\Framework\Event\Observer $observer){
		if (/*$helper->getCanSendItorisEmail()*/ true) {
			/**
			 * 2019-12-15 Dmitry Fedyuk https://github.com/mage2pro
			 * It is the scenario of inline editing of a customer via the Magento's backend customer grid:
			 * https://github.com/royalwholesalecandy/core/issues/29
			 */
			$inlineEdit = $this->getRequest()->getParam('items');
			$login = $this->getRequest()->getParam('login');
			$key = $this->getRequest()->getParam('key');
			$actionName = $this->getRequest()->getActionName();
			if($actionName == 'createpost' || $actionName == 'inlineEdit' || ($actionName == 'save' && isset($key))){
				if(isset($inlineEdit)){
					foreach($inlineEdit as $key=>$value){
						/** @var \Magento\Customer\Model\Customer $customer */
						$customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($value['entity_id']);
						$this->updateUsersTable($customer, $value['status']);
					}
				}else{
					/** @var \Magento\Customer\Model\Customer $customer */
					$customer = $observer->getCustomer();

					$data = $this->getRequest()->getParam('account_admin');
					if (isset($data['status'])) $this->updateUsersTable($customer, $data['status']);
				}
			}else{
				return;
			}
		}
	}
	private function updateUsersTable(\Magento\Customer\Model\Customer $customer, $status){
		/** @var $helper \Itoris\PendingRegistration\Helper\Data */
		$helper = $this->getDataHelper();
		$scope = $helper->getCustomerScope($customer);
		/** @var $db \Magento\Framework\App\ResourceConnection|false */
		$db = $this->getResourceConnection()->getConnection();
		$usersTableName = $this->getResourceConnection()->getTableName('itoris_pendingregistration_users');
		$entity_id = $customer->getEntityId();
		$user = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($entity_id);

		$groups = $helper->getGroups();

		$result = $db->query( 'SELECT status FROM '.$usersTableName.' WHERE customer_id='.$entity_id );
		$current_status =  $result->fetchColumn(0);

		$newStatus = $customer->getConfirmation() && $customer->isConfirmationRequired() ? \Itoris\PendingRegistration\Model\Users::STATUS_NOT_CONFIRMED_BY_EMAIL : intval($status);

		if ((!empty($groups) && !in_array($user->getGroupId(), $groups) && $status === null) || !$helper->isEnabled((int) $customer->getData('store_id'))) {
			$newStatus = \Itoris\PendingRegistration\Model\Users::STATUS_APPROVED;
			$messages = __('Thank you for registration!');
			if(!$this->getDataHelper()->isRepeatMessage($messages->getText())) $this->getMessageManager()->addSuccess($messages);

		}
		if($current_status !== $status ) {
			if($current_status === false){
				$db->query( 'INSERT INTO '.$usersTableName.' SET customer_id='.$entity_id.', status='. $newStatus);
			}
			$db->query('UPDATE '.$usersTableName.' SET status='.$newStatus.' WHERE customer_id='.$entity_id);
			if($status != 0){
				if ($status == 1) {
					$templateId = \Itoris\PendingRegistration\Model\Template::$EMAIL_APPROVED;
					/* Insightly API Call Code Start*/
					if(!$this->isCustomerExistInCrm($customer)){
						$customerData = $this->getCustomerData($customer);
						//print_r($customerData);die;
						$apiUrl = 'https://api.insight.ly/v2.2/Contacts';
						$this->callInsightlyApi($apiUrl, $customerData, 'POST');
					}

					/* Insightly API Call Code End*/
				} else if($status == 2) {
					$templateId = \Itoris\PendingRegistration\Model\Template::$EMAIL_DECLAINED;
				}
				$helper->sendEmail($templateId, $customer, $scope);
			}else{
				if(!$this->emailSendFlag){
					$helper->sendEmail(\Itoris\PendingRegistration\Model\Template::$EMAIL_REG_TO_USER, $customer, $scope);
					$helper->sendEmail(\Itoris\PendingRegistration\Model\Template::$EMAIL_REG_TO_ADMIN, $customer, $scope);
				}
				$this->emailSendFlag = true;
			}
		}

		if (!$customer->getConfirmation()) {
			//it's won't saved, just disallow welcome email
			$customer->setConfirmation(true);
		}
	}
	function isCustomerExistInCrm($customer){
		//echo 'test';die;
		$apiUrl = 'https://api.insight.ly/v2.2/Contacts/Search?email=' . $customer->getEmail();
		$resultData = $this->callInsightlyApi($apiUrl, '', '');
		
		
		if(!empty($resultData) && isset($resultData[0]) && $resultData[0]->CONTACT_ID){
			
		}else{
			return false;
		}
	}
	function callInsightlyApi($apiUrl, $customerData, $method){
		
		if($customerData){
			$CData       = json_encode($customerData);
		}
		
		$ch = curl_init($apiUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: Basic ' . base64_encode('c9be8713-d7d3-4c9b-9802-fc5394012dcb')
		));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		if($method == 'POST'){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $CData);
		}else{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		curl_close($ch);
		//print_r($result);die;
		if($result){
			return json_decode($result);
		}
		return false;
		
	}
	function getCustomerData($customer){
		$CustomerDetails = array();
		//echo $customer->getId();
		if($customer && $customer->getId()){
			$customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customer->getId());
			$address = $customer->getDefaultBillingAddress();
			$CustomerDetails['FIRST_NAME']       = $customer->getFirstname();
			$CustomerDetails['LAST_NAME']        = $customer->getLastname() . " (" . $customer->getId() . ")";
			$CustomerDetails['DATE_CREATED_UTC'] = $customer->getCreatedAt();
			$CustomerDetails['DATE_UPDATED_UTC'] = $customer->getUpdatedAt();
			$CustomerDetails['ADDRESSES']['0']['ADDRESS_TYPE'] = "PRIMARY";
			if($address){
				$CustomerDetails['ADDRESSES']['0']['STREET']       = $address->getStreetLine(1).' '.$address->getStreetLine(2);
				$CustomerDetails['ADDRESSES']['0']['CITY']         = $address->getCity();
				$CustomerDetails['ADDRESSES']['0']['STATE']        = $address->getRegion();
				$CustomerDetails['ADDRESSES']['0']['POSTCODE']     = $address->getPostcode();
				$country = $this->_objectManager->create(\Magento\Directory\Model\Country::class)->loadByCode($address->getCountryId());
				$CustomerDetails['ADDRESSES']['0']['COUNTRY'] = $country->getName();
			}
			
			$customFields = $this->_objectManager->create('Itoris\RegFields\Model\Customer')->loadOptionsByCustomerId($customer->getId()); /** @var array(string => string) $customFields */
			/**
			 * 2019-12-13 Dmitry Fedyuk https://github.com/mage2pro
			 * «Undefined variable: businesstype
			 * in app/code/Itoris/PendingRegistration/Observers/Save/SaveCustomer.php on line 211»:
			 * https://github.com/royalwholesalecandy/core/issues/27
			 * 2019-12-14 Dmitry Fedyuk https://github.com/mage2pro
			 * "Approving customers via the backend's customers grid does not work":
			 * https://github.com/royalwholesalecandy/core/issues/29
			 */
			list(
				$annualspend, $businesstype, $etaorder, $iteminterest, $lengthinbusiness
				,$minimumorder, $orderfrequency, $reasonforvisiting, $referral, $referredname
			) = array_values(array_map('strval', dfa_select_ordered($customFields, [
				'annualspend', 'businesstype', 'etaorder', 'iteminterest', 'lengthinbusiness'
				,'minimumorder', 'orderfrequency', 'reasonforvisiting', 'referral', 'referredname'
			])));
			if ($businesstype != "") {
				if ($businesstype == "1") {
					$businesstypeValue = "Amusements/Attractions";
				} elseif ($businesstype == "2") {
					$businesstypeValue = "Bakery, Cake, and Candy Supplier";
				} elseif ($businesstype == "3") {
					$businesstypeValue = "Coffee Shop";
				} elseif ($businesstype == "4") {
					$businesstypeValue = "Deli/Convenience Stores";
				} elseif ($businesstype == "5") {
					$businesstypeValue = "Ecommerce";
				} elseif ($businesstype == "6") {
					$businesstypeValue = "Event Planner/Caterer";
				} elseif ($businesstype == "7") {
					$businesstypeValue = "Gift Shop/Florist";
				} elseif ($businesstype == "8") {
					$businesstypeValue = "Ice Cream, Chocolate, and Candy Store";
				} elseif ($businesstype == "9") {
					$businesstypeValue = "Other";
				} else {
					$businesstypeValue = "";
				}
				$CustomerDetails['CUSTOMFIELDS']['0']['CUSTOM_FIELD_ID'] = "CONTACT_FIELD_2";
				$CustomerDetails['CUSTOMFIELDS']['0']['FIELD_VALUE']     = $businesstypeValue;
			}
			 if ($lengthinbusiness != "") {
				if ($lengthinbusiness == "1") {
					$lengthinbusinessValue = "New Store";
				} elseif ($lengthinbusiness == "2") {
					$lengthinbusinessValue = "Less than 1 year";
				} elseif ($lengthinbusiness == "3") {
					$lengthinbusinessValue = "1-5 years";
				} elseif ($lengthinbusiness == "4") {
					$lengthinbusinessValue = "6-10 years";
				} elseif ($lengthinbusiness == "5") {
					$lengthinbusinessValue = "10+ years";
				} else {
					$lengthinbusinessValue = "";
				}
				$CustomerDetails['CUSTOMFIELDS']['1']['CUSTOM_FIELD_ID'] = "CONTACT_FIELD_3";
				$CustomerDetails['CUSTOMFIELDS']['1']['FIELD_VALUE']     = $lengthinbusinessValue;
			}
			if ($iteminterest != "") {
				if ($iteminterest == "1") {
					$iteminterestValue = "Baking Supplies";
				} elseif ($iteminterest == "2") {
					$iteminterestValue = "Beverages";
				} elseif ($iteminterest == "3") {
					$iteminterestValue = "Candy";
				} elseif ($iteminterest == "4") {
					$iteminterestValue = "Chocolate";
				} elseif ($iteminterest == "5") {
					$iteminterestValue = "Nuts & Snacks";
				} elseif ($iteminterest == "6") {
					$iteminterestValue = "Other";
				} elseif ($iteminterest == "7") {
					$iteminterestValue = "Merckens Chocolate";
				} elseif ($iteminterest == "8") {
					$iteminterestValue = "Wilbur Chocolate";
				} elseif ($iteminterest == "9") {
					$iteminterestValue = "Peters Chocolate";
				}elseif ($iteminterest == "10") {
					$iteminterestValue = "Guittard";
				} elseif ($iteminterest == "11") {
					$iteminterestValue = "Clasen";
				} elseif ($iteminterest == "12") {
					$iteminterestValue = "Barry Callebaut";
				} elseif ($iteminterest == "13") {
					$iteminterestValue = "Blommer";
				} elseif ($iteminterest == "14") {
					$iteminterestValue = "Van Leer";
				} else {
					$iteminterestValue = "";
				}
				$CustomerDetails['CUSTOMFIELDS']['2']['CUSTOM_FIELD_ID'] = "CONTACT_FIELD_4";
				$CustomerDetails['CUSTOMFIELDS']['2']['FIELD_VALUE']     = $iteminterestValue;
			}
			 if ($minimumorder != "") {
				if ($minimumorder == "1") {
					$minimumorderValue = "Yes";
				} elseif ($minimumorder == "2") {
					$minimumorderValue = "No";
				} else {
					$minimumorderValue = "";
				}
				$CustomerDetails['CUSTOMFIELDS']['3']['CUSTOM_FIELD_ID'] = "CONTACT_FIELD_5";
				$CustomerDetails['CUSTOMFIELDS']['3']['FIELD_VALUE']     = $minimumorderValue;
			}  
			if ($annualspend != "") {
				if ($annualspend == "1") {
					$annualspendValue = "$300-$10000";
				} elseif ($annualspend == "2") {
					$annualspendValue = "$10000-$25000";
				} elseif ($annualspend == "3") {
					$annualspendValue = "over $25000";
				} else {
					$annualspendValue = "";
				}
				 $CustomerDetails['CUSTOMFIELDS']['4']['CUSTOM_FIELD_ID'] = "CONTACT_FIELD_6";
				 $CustomerDetails['CUSTOMFIELDS']['4']['FIELD_VALUE']     = $annualspendValue;
			} 
			   if ($orderfrequency != "") {
				if ($orderfrequency == "1") {
					$orderfrequencyValue = "Weekly";
				} elseif ($orderfrequency == "2") {
					$orderfrequencyValue = "More than once a month";
				} elseif ($orderfrequency == "3") {
					$orderfrequencyValue = "Monthly";
				} elseif ($orderfrequency == "4") {
					$orderfrequencyValue = "Yearly";
				} else {
					$orderfrequencyValue = "";
				}
				$CustomerDetails['CUSTOMFIELDS']['5']['CUSTOM_FIELD_ID'] = "CONTACT_FIELD_7";
				$CustomerDetails['CUSTOMFIELDS']['5']['FIELD_VALUE']     = $orderfrequencyValue;
			} 
			if ($etaorder != "") {
				if ($etaorder == "1") {
					$etaorderValue = "Upon Approval";
				} elseif ($etaorder == "2") {
					$etaorderValue = "Within the week";
				} elseif ($etaorder == "3") {
					$etaorderValue = "Within the month";
				} else {
					$etaorderValue = "";
				}
				$CustomerDetails['CUSTOMFIELDS']['6']['CUSTOM_FIELD_ID'] = "CONTACT_FIELD_8";
				$CustomerDetails['CUSTOMFIELDS']['6']['FIELD_VALUE']     = $etaorderValue;
			}
			if ($reasonforvisiting != "") {
				if ($reasonforvisiting == "1") {
					$reasonforvisitingValue = "Dissatified with current supplier";
				} elseif ($reasonforvisiting == "2") {
					$reasonforvisitingValue = "New store looking for supplier";
				} else {
					$reasonforvisitingValue = "";
				}
				$CustomerDetails['CUSTOMFIELDS']['7']['CUSTOM_FIELD_ID'] = "CONTACT_FIELD_10";
				$CustomerDetails['CUSTOMFIELDS']['7']['FIELD_VALUE']     = $reasonforvisitingValue;
			} 
			if ($referral != "") {
				if ($referral == "1") {
					$referralValue = "Internet Search";
				} elseif ($referral == "2") {
					$referralValue = "Candy Show/Event";
				} elseif ($referral == "3") {
					$referralValue = "Email";
				} elseif ($referral == "4") {
					$referralValue = "Message boards/Blogs";
				} elseif ($referral == "5") {
					$referralValue = "Supplier Listings";
				}elseif ($referral == "6") {
					$referralValue = "Referral";
				} elseif ($referral == "7") {
					$referralValue = "Other";
				} else {
					$referralValue = "";
				}
				$CustomerDetails['CUSTOMFIELDS']['8']['CUSTOM_FIELD_ID'] = "CONTACT_FIELD_11";
				$CustomerDetails['CUSTOMFIELDS']['8']['FIELD_VALUE']     = $referralValue;
			}
			if ($referredname != "") {
				$CustomerDetails['CUSTOMFIELDS']['9']['CUSTOM_FIELD_ID'] = "CONTACT_FIELD_1";
				$CustomerDetails['CUSTOMFIELDS']['9']['FIELD_VALUE']     = $referredname;
			}
			$CustomerDetails['CONTACTINFOS']['0']['TYPE']   = "EMAIL";
			$CustomerDetails['CONTACTINFOS']['0']['DETAIL'] = $customer->getEmail();
			$CustomerDetails['CONTACTINFOS']['0']['LABEL']  = "HOME";
			if($address && $address->getTelephone()){
				$CustomerDetails['CONTACTINFOS']['1']['TYPE']   = "PHONE";
				$CustomerDetails['CONTACTINFOS']['1']['DETAIL'] = $address->getTelephone();
				$CustomerDetails['CONTACTINFOS']['1']['LABEL']  = "HOME";
			}
		}
		//print_r($CustomerDetails);
		return $CustomerDetails;
	}
}