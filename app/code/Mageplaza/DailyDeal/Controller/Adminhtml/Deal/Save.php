<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Controller\Adminhtml\Deal;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Mageplaza\DailyDeal\Controller\Adminhtml\Deal;
use Mageplaza\DailyDeal\Helper\Data as HelperData;
use Mageplaza\DailyDeal\Model\DealFactory;

/**
 * Class Save
 * @package Mageplaza\DailyDeal\Controller\Adminhtml\Deal
 */
class Save extends Deal
{
    /**
     * Date filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Mageplaza\DailyDeal\Model\DealFactory $dealFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Mageplaza\DailyDeal\Helper\Data $helperData
     */
    public function __construct(
        Context $context,
        DealFactory $dealFactory,
        Registry $coreRegistry,
        Date $dateFilter,
        HelperData $helperData
    )
    {
        $this->_dateFilter = $dateFilter;
        $this->_helperData = $helperData;

        parent::__construct($context, $dealFactory, $coreRegistry);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('deal')) {
            $deal    = $this->_initDeal();
            $sku     = $data['product_sku'];
            $dealQty = (int)$data['deal_qty'];

            try {
                $dealCollection = $this->_dealFactory->create()->getCollection()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('product_id', ['eq' => $data['product_id']]);

                if ($dealCollection->getSize() == 0 || $deal->getId()) {
                    if ($this->_helperData->getProductQty($sku) >= $dealQty) {
                        $deal->addData($data)->save();
                        $this->messageManager->addSuccessMessage(__('The Deal has been saved.'));

                        if ($this->getRequest()->getParam('back')) {
                            $resultRedirect->setPath('*/*/edit', ['id' => $deal->getId(), '_current' => true]);

                            return $resultRedirect;
                        }
                    } else {
                        $this->messageManager->addError(__("Deal qty must be less than or equal to product qty"));
                        $resultRedirect->setPath('*/*/edit', ['id' => $deal->getId(), '_current' => true]);

                        return $resultRedirect;
                    }
                } else {
                    $this->messageManager->addError("Already set Deal for this product.");
                    $resultRedirect->setPath('*/*/edit', ['id' => $deal->getId(), '_current' => true]);

                    return $resultRedirect;
                }
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Deal. %1', $e->getMessage()));
                $resultRedirect->setPath('*/*/edit', ['id' => $deal->getId(), '_current' => true]);

                return $resultRedirect;
            }
        }
        $resultRedirect->setPath('*/*/');

        return $resultRedirect;
    }
}
