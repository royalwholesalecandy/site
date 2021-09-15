<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Akeans\Pocustomize\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ViewAction
 */
class PaidAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
					$invoice= $objectManager->create('Magento\Sales\Model\Order\Invoice')->load($item['entity_id']);
					if($invoice->getState() == 1){
						$viewUrlPath = $this->getData('config/paidUrlPath') ?: '#';
						$urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'entity_id';
						$item[$this->getData('name')] = [
							'paid' => [
								'href' => $this->urlBuilder->getUrl(
									$viewUrlPath,
									[
										$urlEntityParamName => $item['entity_id']
									]
								),
								'label' => __('Mark As Paid')
							]
						];
					}else{
						$item[$this->getData('name')] = [
							'paid' => [
								'href' => '#',
								'label' => __('-')
							]
						];
					}
                    
                }
            }
        }

        return $dataSource;
    }
}
