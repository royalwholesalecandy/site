<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\AttributeMap\Edit;

class AttributeMap extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_AmazonMagentoConnect::AttributeMap/attribute-template.phtml';

    /**
     * @var \Webkul\AmazonMagentoConnect\Model\AttributeMapFactory $attributeMap
     */
    private $attributeMap;

    /**
     * @param \Magento\Backend\Block\Template\Context $context,
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper,
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria,
     * @param \Magento\Framework\Registry $registry,
     * @param \Webkul\AmazonMagentoConnect\Model\AttributeMapFactory $attributeMap,
     * @param \Webkul\AmazonMagentoConnect\Logger\Logger $logger,
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria,
        \Magento\Framework\Registry $registry,
        \Webkul\AmazonMagentoConnect\Model\AttributeMapFactory $attributeMap,
        \Webkul\AmazonMagentoConnect\Logger\Logger $logger,
        \Webkul\AmazonMagentoConnect\Model\Config\Source\AmazonProAttribute $amazonProAttribute,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productAttributeRepository = $productAttributeRepository;
        $this->jsonHelper = $jsonHelper;
        $this->searchCriteria = $searchCriteria;
        $this->coreRegistry = $registry;
        $this->attributeMap = $attributeMap;
        $this->logger = $logger;
        $this->amazonProAttribute = $amazonProAttribute;
    }

    /**
     * getMappedVariables
     */
    public function getMappedVariables()
    {
        try {
            $attributeMap = $this->attributeMap->create()->getCollection();
            return $attributeMap->toArray();
        } catch (\Exception $e) {
            $this->logger->addError('getMappedVariables : '. $e->getMessage());
            return [];
        }
    }

    /**
     * get Amazon product attributes
     *
     * @return array
     */
    public function getAmazonProAttributes()
    {
        return $this->amazonProAttribute->toArray();
    }

    /**
     * getProductAttributeList
     * @return json
     */
    public function getProductAttributeList()
    {
        try {
            $searchCriteria = $this->searchCriteria->addFilter(
                'frontend_input',
                ['select', 'text', 'date', 'multiline', 'textarea', 'multiselect', 'price', 'weight', 'boolean'],
                'in'
            )->create();
            $attributeList = $this->productAttributeRepository->getList($searchCriteria)->getItems();
            return $attributeList;
        } catch (\Exception $e) {
            $this->logger->addError('getProductAttributeList : '. $e->getMessage());
            return [];
        }
    }
}
