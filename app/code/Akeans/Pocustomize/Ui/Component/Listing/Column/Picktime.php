<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Akeans\Pocustomize\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class Picktime extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;

    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, OrderRepositoryInterface $orderRepository, SearchCriteriaBuilder $criteria, array $components = [], array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $order  = $this->_orderRepository->get($item["entity_id"]);
				$totalQty = 0;
				$pickTime = 0;
				foreach($order->getAllItems() as $oItem){
					$totalQty += $oItem->getQtyOrdered();
				}
				$totalLineItemQty = count($order->getAllItems()) + $totalQty;
				if($totalLineItemQty > 1){
					//$pickTime = (ceil($totalLineItemQty/6)) * 2;
                    $pickTime = (ceil($totalLineItemQty/6)+1) * 2;
				}
				
                $item[$this->getData('name')] = ceil($pickTime);
            }
        }

        return $dataSource;
    }
}
