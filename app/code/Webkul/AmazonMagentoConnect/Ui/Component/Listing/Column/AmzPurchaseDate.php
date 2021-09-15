<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use Webkul\AmazonMagentoConnect\Api\OrderMapRepositoryInterface;

class AmzPurchaseDate extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        OrderMapRepositoryInterface $orderMapRepo,
        array $components = [],
        array $data = []
    ) {
        $this->_orderMapRepo = $orderMapRepo;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $collection = $this->_orderMapRepo->getByMagentoOrderId($item['increment_id'])->getFirstItem();

                if ($collection->getEntityId()) {
                    $item[$this->getData('name')] = date("F d, Y", strtotime($collection->getPurchaseDate()));
                } else {
                    $item[$this->getData('name')] = 'Not an Amazon Order';
                }
            }
        }

        return $dataSource;
    }
}
