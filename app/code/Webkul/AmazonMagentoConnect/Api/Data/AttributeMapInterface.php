<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Api\Data;

interface AttributeMapInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const MAGE_ATTR = 'mage_attr';
    const AMZ_ATTR = 'amz_attr';
    const STATUS = 'status';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * set ID.
     *
     * @return $this
     */
    public function setId($entityId);

   /**
    * Get MageAttr.
    * @return string
    */
    public function getMageAttr();

   /**
    * set price.
    * @return $this
    */
    public function setMageAttr($mageAttr);

   /**
    * Get AmzAttr.
    * @return string
    */
    public function getAmzAttr();

   /**
    * set price.
    * @return $this
    */
    public function setAmzAttr($mageAttr);

   /**
    * Get status.
    * @return string
    */
    public function getStatus();

   /**
    * set status.
    * @return $this
    */
    public function setStatus($status);
}
