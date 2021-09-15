<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\AmazonMagentoConnect\Api\OrderMapRepositoryInterface;
use Webkul\AmazonMagentoConnect\Api\AmazonTempDataRepositoryInterface;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\Order;

class CreateOrder extends Order
{
    /**
     * @var \Webkul\AmazonMagentoConnect\Model\Ordermap
     */
    private $orderMapRecord;

    /**
     * @var \Webkul\AmazonMagentoConnect\Helper\Order
     */
    private $orderData;

    /**
     * @var OrdermapRepositoryInterface
     */
    private $orderMapRepository;

    /**
     * @var AmazonTempDataRepositoryInterface
     */
    private $amazonTempDataRepo;

    /**
     * @param Context                                     $context
     * @param \Webkul\AmazonMagentoConnect\Model\OrderMap $orderMapRecord
     * @param OrderMapRepositoryInterface                 $orderMapRepository
     * @param \Webkul\AmazonMagentoConnect\Helper\Order   $orderData
     * @param AmazonTempDataRepositoryInterface           $amazonTempDataRepo
     */
    public function __construct(
        Context $context,
        \Webkul\AmazonMagentoConnect\Model\OrderMap $orderMapRecord,
        OrderMapRepositoryInterface $orderMapRepository,
        \Webkul\AmazonMagentoConnect\Helper\Order $orderData,
        AmazonTempDataRepositoryInterface $amazonTempDataRepo,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->orderMapRecord = $orderMapRecord;
        $this->orderData = $orderData;
        $this->orderMapRepository = $orderMapRepository;
        $this->amazonTempDataRepo = $amazonTempDataRepo;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $accountId = $this->getRequest()->getParam('accountId');
        $this->helper->getAmzClient($accountId);
        $tempData = $this->helper
            ->getTotalImported('order', $accountId);
        if ($tempData) {
            $backendSession = $this->_objectManager->get(
                '\Magento\Backend\Model\Session'
            );
            $backendSession->setAmzSession('start');
            $tempOrder = json_decode($tempData->getItemData(), true);
            $mapedOrder = $this->orderMapRepository
                        ->getCollectionByAmzOrderId($tempOrder['amz_order_id']);

            if (!$this->helper->getAmazonAccountId($mapedOrder)) {
                //Create order in store as Amazon
                $result = $this->orderData
                    ->createAmazonOrderAtMage($tempOrder, $accountId);
                if (isset($result['order_id']) && $result['order_id']) {
                    $data = [
                        'amazon_order_id' => $tempOrder['amz_order_id'],
                        'mage_order_id' => $result['order_id'],
                        'status' => $tempOrder['order_status'],
                        'mage_amz_account_id'   => $accountId,
                        'purchase_date' => $tempData->getPurchaseDate(),
                        'fulfillment_channel'   => $tempOrder['fulfillment_channel']
                    ];
                    $record = $this->orderMapRecord;
                    $record->setData($data)->save();
                }
            } else {
                $result = [
                    'error' => 1,
                    'msg' => __('Amazon order ').$tempOrder['amz_order_id'].
                            __(' already maped with store order #').$mapedOrder->getMageOrderId()
                ];
            }

            $tempData->delete();
            $backendSession->setAmzSession('');
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
            );
        } else {
            $data = $this->getRequest()->getParams();
            $total = (int) $data['count'] - (int) $data['skip'];
            $msg = '<div class="wk-mu-success wk-mu-box">'.__('Total ').$total.__(' Order(s) Imported.').'</div>';
            $msg .= '<div class="wk-mu-note wk-mu-box">'.__('Finished Execution.').'</div>';
            $result['msg'] = $msg;
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
            );
        }
    }
}
