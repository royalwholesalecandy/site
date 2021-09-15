<?php namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer;

class ChronopostLabel extends RendererAbstract
{
    public function getShippingLabelFile($ordersInProgress, $carrierTemplate)
    {
        foreach($ordersInProgress as $orderInProgress) {
            $shipment = $orderInProgress->getShipment();
            $labelPdf = $this->getLabelPdf($shipment);
            if(!$labelPdf){
                throw new \Exception(__('An error occurred during the generation of the Chronopost label'));
            }
            return $labelPdf;
        }
    }

    public function getLabelPdf($shipment)
    {
        $labelHelper = $this->getObjectManager()->create('Chronopost\Chronorelais\Helper\Shipment');
        return $labelHelper->getEtiquetteUrl($shipment);
    }

}
