<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\PriceRule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Webkul\AmazonMagentoConnect\Model\PriceRuleFactory;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\PriceRule;

class Save extends PriceRule
{
     /**
      * @var \Magento\Framework\Controller\Result\JsonFactory
      */
    private $resultJsonFactory;

     /**
      * @var \Webkul\AmazonMagentoConnect\Model\PriceRuleFactory
      */
    private $priceRuleFactory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        PriceRuleFactory $priceRuleFactory,
        TimezoneInterface $localeDate
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->priceRuleFactory = $priceRuleFactory;
        $this->localeDate = $localeDate;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('entity_id');
        $data = $this->getRequest()->getParams();
        $time = $this->localeDate->date()->format('Y-m-d H:i:s');
        $data['created_at'] = $time;
        if (!$data) {
            $this->_redirect('*/*/');
            return;
        }

        $errors = $this->validateFormFields($data);
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->messageManager->addError($error);
                $this->_redirect('*/*/');
                return;
            }
        }
        if (empty($id) && !$this->checkAvailabilityOfRule($data['price_from'], $data['price_to'])) {
            $this->messageManager->addError(__('Price range already exist.'));
            $this->_redirect('*/*/');
            return;
        }
        $model = $this->priceRuleFactory->create()->load($id);

        if ($id && $model->isObjectNew()) {
            $this->messageManager->addError(__('This rule is no longer exist.'));
            $this->_redirect('*/*/');
            return;
        }

        try {
            $id = $model->setData($data)->save()->getId();
            $this->messageManager->addSuccess(__('You saved Amazon Product Price Rule.'));
        } catch (\Exception $e) {
            $this->messageManager->addMessages(__('something went wrong'));
            $this->_redirect('*/*/');
        }
        $this->redirectToEdit($data, $id);
    }

    /**
     * @param \Magento\User\Model\User $model
     * @param array $data
     * @return void
     */
    protected function redirectToEdit(array $data, $id)
    {
        $this->_getSession()->setAmzAccountData($data);
        $data['entity_id'] = $id;
        $arguments = $data['entity_id'] ? ['id' => $data['entity_id']]: [];
        $arguments = array_merge(
            $arguments,
            ['_current' => true, 'active_tab' => $data['active_tab']]
        );
        if (isset($data['entity_id']) && isset($data['back'])) {
            $this->_redirect('*/*/edit', $arguments);
        } else {
            $this->_redirect('*/*/index', $arguments);
        }
    }

    /**
     * validate form fields
     *
     * @param array $wholeData
     * @return array
     */
    private function validateFormFields($wholeData)
    {
        $errors = [];
        $data = [];
        foreach ($wholeData as $code => $value) {
            switch ($code) :
                case 'price_from':
                    $result = $this->priceValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('price_from should contain only decimal numbers');
                    }

                    break;
                case 'price_to':
                    $result = $this->priceValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('price_to should contain only decimal numbers');
                    }
                    break;
                case 'price':
                    $this->priceValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('price should contain only decimal numbers');
                    }
                    break;
            endswitch;
        }
        return $errors;
    }
    
    /**
     * price validation function
     *
     * @param string $value
     * @param string $code
     * @param string $data
     * @return array
     */
    private function priceValidateFunction($value, $code, $data)
    {
        $error = false;
        if (!preg_match('/^([0-9])+?[0-9.,]*$/', $value)) {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }

    /**
     * check status of price range
     *
     * @param [type] $minPrice
     * @param [type] $maxPrice
     * @return void
     */
    private function checkAvailabilityOfRule($minPrice, $maxPrice)
    {
        $collection = $this->_objectManager
                    ->create('Webkul\AmazonMagentoConnect\Model\PriceRuleFactory')
                    ->create()
                    ->getCollection();
        $collection->getSelect()->where(
            'price_from < '.$minPrice.' AND '.$maxPrice.' < price_to
            OR '.$minPrice.' BETWEEN price_from AND price_to 
            OR '.$maxPrice.' BETWEEN price_from AND price_to'
        );
        if ($collection->getSize()) {
            return false;
        }
        return true;
    }
}
