<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer;

class ColissimoLabel extends RendererAbstract
{
    public function getShippingLabelFile($ordersInProgress, $carrierTemplate){


        foreach($ordersInProgress as $orderInProgress)
        {
            $shipment = $orderInProgress->getShipment();
            if ($shipment)
            {
                $labelPath = $this->getLabelPath($shipment);
                if (!file_exists($labelPath))
                {
                    $trackingNumber = $this->createLabel($orderInProgress);
                    $this->attachTrackingToShipment($shipment, $trackingNumber);
                }


                $content = file_get_contents($labelPath);
                return $content;    //return the first label !
            }
        }

    }

    public function getLabelPath($shipment)
    {
        $labelHelper = $this->getObjectManager()->create('Colissimo\Label\Helper\Data');
        $labelPath = $labelHelper->getLabelPath() . $shipment->getOrder()->getIncrementId() . '.pdf';
        return $labelPath;
    }

    public function createLabel($orderInProgress)
    {
        $shipment = $orderInProgress->getShipment();

        $address = $shipment->getOrder()->getShippingAddress();
        if (!$address)
            $shipment->getOrder()->getBillingAddress();

        $streets = $address->getstreet();

        $data = (
            [
                'order_shipment'                        => $shipment,
                'store_id'                              => $shipment->getStoreId(),
                'base_currency_code'                    => $shipment->getOrder()->getBaseCurrencyCode(),
                'package_weight'                        => 3,   //$orderInProgress->getip_weights(),
                'packages'                              => null,
                'shipping_method'                       => str_replace('colissimo_', '', $shipment->getOrder()->getShippingMethod()),
                'shipper_contact_person_name'           => 'admin admin',
                'shipper_contact_person_first_name'     => 'admin',
                'shipper_contact_person_last_name'      => 'admin',
                'shipper_contact_company_name'          => $this->_config->getGlobalSetting('shipping/colissimo_label/commercial_name', $shipment->getStoreId()),
                'shipper_contact_phone_number'          => $this->_config->getGlobalSetting('general/store_information/phone', $shipment->getStoreId()),
                'shipper_address_street'                => $this->_config->getGlobalSetting('shipping/origin/street_line1', $shipment->getStoreId()),
                'shipper_address_street_1'              => $this->_config->getGlobalSetting('shipping/origin/street_line1', $shipment->getStoreId()),
                'shipper_address_street_2'              => $this->_config->getGlobalSetting('shipping/origin/street_line2', $shipment->getStoreId()),
                'shipper_address_city'                  => $this->_config->getGlobalSetting('shipping/origin/city', $shipment->getStoreId()),
                'shipper_address_state_or_province_code'=> substr($this->_config->getGlobalSetting('shipping/origin/postcode', $shipment->getStoreId()), 0, 2),
                'shipper_address_postal_code'           => $this->_config->getGlobalSetting('shipping/origin/postcode', $shipment->getStoreId()),
                'shipper_address_country_code'           => $this->_config->getGlobalSetting('shipping/origin/country_id', $shipment->getStoreId()),
                'recipient_contact_person_name'         => $address->getlastname().' '.$address->getfirstname(),
                'recipient_contact_person_first_name'   => $address->getfirstname(),
                'recipient_contact_person_last_name'    => $address->getlastname(),
                'recipient_contact_company_name'        => $address->getcompany(),
                'recipient_contact_phone_number'        => $address->gettelephone(),
                'recipient_email'                       => $address->getemail(),
                'recipient_address_street'              => implode(' ', $streets),
                'recipient_address_street_1'            => $streets[0],
                'recipient_address_street_2'            => isset($streets[1]) ? $streets[1] : '',
                'recipient_address_city'                => $address->getcity(),
                'recipient_address_state_or_province_code'=> $address->getRegionCode(),
                'recipient_address_region_code'         => $address->getRegionCode(),
                'recipient_address_postal_code'         => $address->getpostcode(),
                'recipient_address_country_code'         => $address->getcountry_id(),
                'shipper_email'                         => '',
            ]
        );

        $request = $this->getObjectManager()->create('Magento\Shipping\Model\Shipment\Request');
        $request->setData($data);

        $this->eventManager->dispatch(
            'colissimo_return_label_before',
            ['request' => $request, 'order' => $shipment->getOrder()]
        );

        $labelModel = $this->getObjectManager()->create('Colissimo\Label\Model\Label');
        $response = $labelModel->doShipmentRequest($request);

        if ($response->getData('errors'))
            throw new \Exception(__('An error occured during label generation : %1', $response->getData('errors')));

        $info  = $response->getData('info');
        $label = reset($info);


        $this->eventManager->dispatch(
            'colissimo_return_label_after',
            [
                'request'  => $request,
                'order'    => $shipment->getOrder(),
                'label'    => $label,
            ]
        );

        $trackingNumber = $info[0]['tracking_number'];
        return $trackingNumber;
    }

    protected function attachTrackingToShipment($shipment, $trackingNumber)
    {
        $track = $this->getObjectManager()->create('Magento\Sales\Model\Order\Shipment\Track');

        $shippingMethod = $shipment->getOrder()->getShippingMethod();
        list($carrierCode, $method) = explode('_', $shippingMethod, 2);

        $data = array(
            'carrier_code' => $carrierCode,
            'title' => $shipment->getOrder()->getShippingDescription(),
            'number' => $trackingNumber
        );
        $track->addData($data);

        $shipment->addTrack($track);
        $track->save();
    }

}
