<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Api\Data;

interface ProductMapInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const AMAZON_PRO_ID = 'amazon_pro_id';
    const NAME = 'name';
    const PRODUCT_TYPE = 'product_type';
    const MAGENTO_PRO_ID = 'magento_pro_id';
    const MAGE_CAT_ID = 'mage_cat_id';
    const CHANGE_STATUS = 'change_status';
    const FEEDSUBMISSION_ID = 'feedsubmission_id';
    const EXPORT_STATUS = 'export_status';
    const ERROR_STATUS = 'error_status';
    const PRO_STATUS_AT_AMZ = 'pro_status_at_amz';
    const CREATED_AT = 'created';

    /**
     * Get submissionId.
     * @return int|null
     */
    public function getFeedsubmissionId();

    /**
     * set submissionId.
     * @return $this
     */
    public function setFeedsubmissionId($submissionId);

    /**
     * Get exportStatus.
     * @return int|null
     */
    public function getExportStatus();

    /**
     * set exportStatus.
     * @return $this
     */
    public function setExportStatus($exportStatus);
    /**
     * Get errorStatus.
     * @return int|null
     */
    public function getErrorStatus();

    /**
     * set errorStatus.
     * @return $this
     */
    public function setErrorStatus($errorStatus);

    /**
     * Get proStatus.
     * @return int|null
     */
    public function getProStatusAtAmz();

    /**
     * set proStatus.
     * @return $this
     */
    public function setProStatusAtAmz($proStatus);

    /**
     * Get ID.
     * @return int|null
     */
    public function getId();

    /**
     * set ID.
     * @return $this
     */
    public function setId($id);

    /**
     * Get AmazonProId.
     * @return string
     */
    public function getAmazonProId();

    /**
     * set AmazonProId.
     * @return $this
     */
    public function setAmazonProId($amzProId);

    /**
     * Get Name.
     * @return string
     */
    public function getName();

    /**
     * set Name.
     * @return $this
     */
    public function setName($name);

    /**
     * Get ProductType.
     * @return string
     */
    public function getProductType();

    /**
     * set ProductType.
     * @return $this
     */
    public function setProductType($productType);

    /**
     * Get MagentoProId.
     * @return string
     */
    public function getMagentoProId();

    /**
     * set MagentoProId.
     * @return $this
     */
    public function setMagentoProId($magentoProId);

    /**
     * Get MageCatId.
     * @return string
     */
    public function getMageCatId();

    /**
     * set MageCatId.
     * @return $this
     */
    public function setMageCatId($mageCatId);

    /**
     * Get ChangeStatus.
     * @return string
     */
    public function getChangeStatus();

    /**
     * set ChangeStatus.
     * @return $this
     */
    public function setChangeStatus($changeStatus);

    /**
     * Get CreatedAt.
     * @return string
     */
    public function getCreatedAt();

    /**
     * set CreatedAt.
     * @return $this
     */
    public function setCreatedAt($created);
}
