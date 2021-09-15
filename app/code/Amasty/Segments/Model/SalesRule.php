<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;

class SalesRule extends \Magento\Rule\Model\AbstractModel
{
    /**
     * @var SalesRule\Condition\CombineFactory
     */
    protected $condCombineFactory;

    /**
     * @var \Magento\Rule\Model\Action\CollectionFactory
     */
    protected $actionCollectionFactory;

    /**
     * @var ResourceModel\Index
     */
    protected $indexResource;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $amastySerializer;

    /**
     * _construct
     */
    protected function _construct()
    {
        $this->amastySerializer = $this->getData('amastySerializer');
        $this->indexResource = $this->getData('indexResource');
        $this->actionCollectionFactory = $this->getData('actionCollectionFactory');
        $this->condCombineFactory = $this->getData('condCombineFactory');

        if ($this->amastySerializer) {
            $this->serializer = $this->amastySerializer;
        }

        parent::_construct();
        $this->_init('Amasty\Segments\Model\ResourceModel\Segment');
        $this->setIdFieldName('segment_id');
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * @return \Magento\Rule\Model\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * @param string $type
     * @param array $segments
     * @param string|int $entityId
     * @return bool
     */
    public function validateByIndex($type, $segments, $entityId)
    {
        $segmentItems = $this->indexResource->checkValidCustomerFromIndex($segments, $entityId, $type);

        return count($segmentItems) > 0;
    }

    /**
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * @param string $formName
     * @return string
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }
}
