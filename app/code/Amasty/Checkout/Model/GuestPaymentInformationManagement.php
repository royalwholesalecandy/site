<?php
namespace Amasty\Checkout\Model;
use Amasty\Checkout\Api\GuestPaymentInformationManagementInterface;
/**
 * 2019-12-13 Dmitry Fedyuk https://github.com/mage2pro
 * «Class 'Amasty\Checkout\Model\CouldNotSaveException' not found
 * in app/code/Amasty/Checkout/Model/GuestPaymentInformationManagement.php:47»:
 * https://github.com/royalwholesalecandy/core/issues/24
 */
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
class GuestPaymentInformationManagement extends \Magento\Checkout\Model\GuestPaymentInformationManagement
	implements GuestPaymentInformationManagementInterface
{
	/**
	 * @var \Amasty\Checkout\Helper\CheckoutData
	 */
	protected $checkoutDataHelper;

	public function __construct(
		\Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement,
		\Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement,
		\Magento\Quote\Api\GuestCartManagementInterface $cartManagement,
		\Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement,
		\Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
		CartRepositoryInterface $cartRepository,
		\Amasty\Checkout\Helper\CheckoutData $checkoutDataHelper
	) {
		parent::__construct(
			$billingAddressManagement, $paymentMethodManagement, $cartManagement, $paymentInformationManagement,
			$quoteIdMaskFactory, $cartRepository
		);
		$this->checkoutDataHelper = $checkoutDataHelper;
	}

	public function savePaymentInformationAndPlaceOrder(
		$cartId,
		$email,
		\Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
		\Magento\Quote\Api\Data\AddressInterface $billingAddress = null,
		$amcheckout = null
	) {
		if($paymentMethod['method'] == 'purchaseorder'){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				throw new CouldNotSaveException(__('You need to loggin for this.'));
				return $result;
			}
		$this->checkoutDataHelper->beforePlaceOrder($amcheckout);

		$result = parent::savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMethod, $billingAddress);

		$amcheckout = $this->_addGuestEmailForNewsletter($amcheckout, $email);

		$this->checkoutDataHelper->afterPlaceOrder($amcheckout);

		return $result;
	}

	protected function _addGuestEmailForNewsletter($data = [], $email)
	{
		if (isset($data['subscribe']) && $data['subscribe']) {
			$data['email'] = $email;
		}

		return $data;
	}
}
