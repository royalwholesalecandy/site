<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */


namespace Amasty\Perm\Ui\Component\MassAction\Dealer;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;
use Amasty\Perm\Model\ResourceModel\Dealer\CollectionFactory;
use Amasty\Perm\Helper\Data as PermHelper;

/**
 * Class Options
 */
class Options implements JsonSerializable
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Additional options params
     *
     * @var array
     */
    protected $_data;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $_urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $_paramName;

    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $_additionalData = [];

    /** @var PermHelper  */
    protected $_permHelper;

    /**
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param PermHelper $permHelper
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        PermHelper $permHelper,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_data = $data;
        $this->_urlBuilder = $urlBuilder;
        $this->_permHelper = $permHelper;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {

        if ($this->_options === null) {
            $this->_options = [];

            /** @var \Amasty\Perm\Model\ResourceModel\Dealer\Collection $collection */
            $collection = $this->_collectionFactory->create()
                ->addUserData();

            if ($this->_permHelper->isBackendDealer()){
                $collection->addFieldToFilter('main_table.entity_id', $this->_permHelper->getBackendDealer()->getId());
            }

            $options = $collection
                ->toUserOptionArray();

            $this->prepareData();

            foreach ($options as $optionCode) {

                $this->_options[$optionCode['value']] = [
                    'type' => 'amasty_perm_dealer_' . $optionCode['value'],
                    'label' => $optionCode['label'],
                ];


                if ($this->_urlPath && $this->_paramName) {
                    $this->_options[$optionCode['value']]['url'] = $this->_urlBuilder->getUrl(
                        $this->_urlPath,
                        [$this->_paramName => $optionCode['value']]
                    );
                }

                $this->_options[$optionCode['value']] = array_merge_recursive(
                    $this->_options[$optionCode['value']],
                    $this->_additionalData
                );
            }


            $this->_options = array_values($this->_options);
        }

        return $this->_options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->_data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->_urlPath = $value;
                    break;
                case 'paramName':
                    $this->_paramName = $value;
                    break;
                default:
                    $this->_additionalData[$key] = $value;
                    break;
            }
        }
    }
}
