<?php namespace BoostMyShop\Organizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class OrderPreparationPickingListPdf implements ObserverInterface
{
    protected $organizerCollection;
    protected $config;

    public function __construct(
        \BoostMyShop\Organizer\Model\ResourceModel\Organizer\CollectionFactory $organizerCollection,
        \BoostMyShop\Organizer\Model\Config $config
    ) {
        $this->organizerCollection = $organizerCollection;
        $this->config = $config;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Zend_Pdf_Exception
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->config->getGlobalSetting('orderpreparation/picking/display_organizers')) {
            return;
        }

        $event = $observer->getEvent();
        $page = $event['page'];
        $pickinglist = $event['pickinglist'];
        $orderId = $event['orderId'];

        $organizers =  $this->getOrganizersFromOrderId($orderId);
        if (count($organizers) > 0) {
            $this->drawOrganizers($pickinglist, $page, $organizers);
        }
    }

    /**
     * @param $pickinglist \BoostMyShop\OrderPreparation\Model\Pdf\PickingList
     * @param $page \Zend_Pdf_Page $page
     * @param $organizers
     * @throws \Zend_Pdf_Exception
     */
    protected function drawOrganizers($pickinglist, $page, $organizers)
    {
        $pickinglist->y -= 20;
        $page->drawText(__('Note(s):'), 30, $pickinglist->y, 'UTF-8');
        $pickinglist->y -= 20;

        foreach ($organizers as $organizer) {
            $text = $organizer['o_title'] .': '. $organizer['o_comments'];
            $lines = $pickinglist->splitTextToSize($text, $page->getFont(), $page->getFontSize(), 500);
            foreach ($lines as $line) {
                $page->drawText($line, 35, $pickinglist->y, 'UTF-8');
                $pickinglist->y -= 15;
            }
            $pickinglist->y -= 10;
        }

        $pickinglist->y -= 35;
    }

    /**
     * @param $orderId
     * @return \BoostMyShop\Organizer\Model\ResourceModel\Organizer\Collection
     */
    protected function getOrganizersFromOrderId($orderId)
    {
        return $this->organizerCollection->create()
            ->setOrder('o_updated', 'DESC')
            ->addObjectFilter('sales_order', $orderId);
    }
}
