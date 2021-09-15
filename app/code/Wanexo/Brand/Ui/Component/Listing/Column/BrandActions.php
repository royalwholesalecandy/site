<?php

namespace Wanexo\Brand\Ui\Component\Listing\Column;

class BrandActions extends \Magento\Ui\Component\Listing\Columns\Column
{
   
    const URL_PATH_EDIT = 'wanexo_brand/brandaction/edit';

   
    const URL_PATH_DELETE = 'wanexo_brand/brandaction/delete';

    
    protected $_urlBuilder;


    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['brand_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->_urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'brand_id' => $item['brand_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->_urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'brand_id' => $item['brand_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "${ $.$data.brand_title }"'),
                                'message' => __('Are you sure you wan\'t to delete the  "${ $.$data.brand_title }" ?')
                            ]
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
