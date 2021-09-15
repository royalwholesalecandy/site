<?php

/**

 * Created by Diensh.

 * Author: Dinesh Kaswan

 */

namespace Amasty\Payrestriction\Plugin;

 

use Magento\Checkout\Model\PaymentInformationManagement as CheckoutPaymentInformationManagement;

use Magento\Framework\Exception\CouldNotSaveException;

use Magento\Framework\Exception\LocalizedException;

use Magento\Quote\Api\CartManagementInterface;

use Psr\Log\LoggerInterface;

 

/**

 * Class PaymentInformationManagement

 */

class PaymentInformationManagement

{

    /**

     * @var CartManagementInterface

     */

    private $cartManagement;

 

    /**

     * @var LoggerInterface

     */

    private $logger;

 

    /**

     * PaymentInformationManagement constructor.

     * @param CartManagementInterface $cartManagement

     * @param LoggerInterface $logger

     */

    public function __construct(

        CartManagementInterface $cartManagement,

        LoggerInterface $logger

    ) {

        $this->cartManagement = $cartManagement;

        $this->logger = $logger;

    }

 

    /**

     * @param CheckoutPaymentInformationManagement $subject

     * @param \Closure $proceed

     * @param $cartId

     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod

     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress

     * @return int

     * @throws CouldNotSaveException

     */

    public function aroundSavePaymentInformationAndPlaceOrder(

        CheckoutPaymentInformationManagement $subject,

        \Closure $proceed,

        $cartId,

        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,

        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null

    ) {

		

        $subject->savePaymentInformation($cartId, $paymentMethod, $billingAddress);

        try {

			if($paymentMethod['method'] == 'purchaseorder'){

			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

			$customerSession = $objectManager->create('Magento\Customer\Model\Session');

			$customer = $customerSession->getCustomer();

			$cartObj = $objectManager->get('\Magento\Checkout\Model\Cart');

			$grandTotal = $cartObj->getQuote()->getGrandTotal();

			$PoStatus = false;

			if($customer->getCustomPoLimit() || $customer->getCustomPoCredit()){

				$totalRemainingLimit = (float)$customer->getCustomPoLimit()-(float)$customer->getCustomPoCredit();

				if($totalRemainingLimit >= $grandTotal){

					$PoStatus = true;

				}

			}

				if(!$PoStatus){

					throw new CouldNotSaveException(__('Sorry, you have surpassed your PO Limit'));

					return $proceed($cartId, $paymentMethod, $billingAddress);

				}

			}
			return $proceed($cartId, $paymentMethod, $billingAddress);
            //$orderId = $this->cartManagement->placeOrder($cartId);

        } catch (LocalizedException $exception) {

            throw new CouldNotSaveException(__($exception->getMessage()));

        } catch (\Exception $exception) {

            $this->logger->critical($exception);

            throw new CouldNotSaveException(

                __('An error occurred on the server. Please try to place the order again.'),

                $exception

            );

        }

        return $subject;

    }

}