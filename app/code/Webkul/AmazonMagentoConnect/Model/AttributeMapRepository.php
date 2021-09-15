<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Model;

use Webkul\AmazonMagentoConnect\Api\Data\AttributeMapInterface;
use Webkul\AmazonMagentoConnect\Model\ResourceModel\AttributeMap\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AttributeMapRepository implements \Webkul\AmazonMagentoConnect\Api\AttributeMapRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\AmazonMagentoConnect\Model\ResourceModel\Accounts
     */
    protected $_resourceModel;

    public function __construct(
        AttributeMapFactory $attributeMapFactory,
        \Webkul\AmazonMagentoConnect\Model\ResourceModel\Accounts\CollectionFactory $collectionFactory,
        \Webkul\AmazonMagentoConnect\Model\ResourceModel\Accounts $resourceModel
    ) {
        $this->_resourceModel = $resourceModel;
        $this->attributeMapFactory = $attributeMapFactory;
        $this->_collectionFactory = $collectionFactory;
    }
}
