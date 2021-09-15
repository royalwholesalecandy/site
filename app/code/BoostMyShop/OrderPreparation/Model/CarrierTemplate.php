<?php

namespace BoostMyShop\OrderPreparation\Model;


class CarrierTemplate extends \Magento\Framework\Model\AbstractModel
{
    const kTypeOrderDetailsExport = 'order_details_export';
    const kTypeSimpleAddressLabel = 'simple_address_label';
    const kTypeColissimoLabel = 'colissimo_label';
    const kTypeChronopostLabel = 'chronopost_label';

    protected $_rendererOrderDetailExport;
    protected $_rendererSimpleAddressLabel;
    protected $_csvTrackingExtractHandler;
    protected $_inProgressFactory;
    protected $_rendererColissimoLabel;
    protected $_rendererChronopostLabel;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\OrderDetailsExport $rendererOrderDetailExport,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\SimpleAddressLabel $rendererSimpleAddressLabel,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\ColissimoLabel $rendererColissimoLabel,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\ChronopostLabel $rendererChronopostLabel,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Extract\CsvTrackingExtractHandler $csvTrackingExtractHandler,
        \BoostMyShop\OrderPreparation\Model\InProgressFactory $inProgressFactory,
        array $data = []
    )
    {
        $this->_rendererOrderDetailExport = $rendererOrderDetailExport;
        $this->_rendererSimpleAddressLabel = $rendererSimpleAddressLabel;
        $this->_rendererColissimoLabel = $rendererColissimoLabel;
        $this->_rendererChronopostLabel = $rendererChronopostLabel;
        $this->_csvTrackingExtractHandler = $csvTrackingExtractHandler;
        $this->_inProgressFactory = $inProgressFactory;
        parent::__construct($context, $registry, null, null, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate');
    }

    public function getShippingLabelFile($ordersInProgress)
    {
        $renderer = null;

        switch($this->getct_type())
        {
            case self::kTypeOrderDetailsExport:
                $renderer = $this->_rendererOrderDetailExport;
                break;
            case self::kTypeSimpleAddressLabel:
                $renderer = $this->_rendererSimpleAddressLabel;
                break;
            case self::kTypeColissimoLabel:
                $renderer = $this->_rendererColissimoLabel;
                break;
            case self::kTypeChronopostLabel:
                $renderer = $this->_rendererChronopostLabel;
                break;
        }

        if (!$renderer)
            throw new \Exception('No renderer available for type shipping label template "'.$this->getct_type().'"');
        else {
            $ordersInProgress = $this->filterOrdersInProgress($ordersInProgress, true);
            return $renderer->getShippingLabelFile($ordersInProgress, $this);
        }

    }

    /**
     * Method executed once the shipment is done for order in progress
     * Todo : implement dedicated classes for this, design is not good here :(
     *
     * @param $orderInProgress
     */
    public function afterShipment($orderInProgress)
    {
        switch($this->getct_type())
        {
            case self::kTypeColissimoLabel:
            case self::kTypeChronopostLabel:
                //force label generation
                $this->getShippingLabelFile([$orderInProgress]);
                break;
            default:
                //nothing by default
                break;
        }

    }

    public function filterOrdersInProgress($ordersInProgress, $hydrate = false)
    {
        $orders = [];

        foreach($ordersInProgress as $orderInProgress)
        {
            if (!$orderInProgress->getShipment())
                continue;
            if ($hydrate)
                $orderInProgress->hydrateWithOrderInformation();
            if (!$this->shippingMethodSupported($orderInProgress->getshipping_method()))
                continue;
            $orders[] = $orderInProgress;
        }

        return $orders;
    }

    public function shippingMethodSupported($code)
    {
        $supportedMethods = unserialize($this->getct_shipping_methods());
        foreach($supportedMethods as $method)
        {
            $pattern = '/'.$method.'/';
            if (preg_match($pattern, $code))
                return true;
        }
        return false;
    }

    public function importTracking($fileContent)
    {
        $stats = ['success' => 0, 'error' => 0];
        $datas = $this->_csvTrackingExtractHandler->extract($fileContent, $this);
        foreach($datas as $data)
        {
            try
            {
                if (!$data['shipment'] || !$data['tracking'])
                    throw new \Exception('Data missing');

                $inProgress = $this->_inProgressFactory->create()->loadByShipmentReference($data['shipment']);
                if (!$inProgress->getId())
                    throw new \Exception('Can not find order in progress');

                $inProgress->addTracking($data['tracking']);
                $stats['success']++;
            }
            catch(\Exception $ex)
            {

                $stats['error']++;
            }
        }

        return $stats;
    }

}