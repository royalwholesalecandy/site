<?php
namespace Wanexo\Mlayer\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Wanexo\Mlayer\Model\Banner\Url;
use Wanexo\Mlayer\Model\Banner\Source\IsActive;
use Magento\Framework\Data\Collection\AbstractDb;

class Banner extends AbstractModel
{
    /**
     * status enabled
     *
     * @var int
     */
    const STATUS_ENABLED = 1;
    /**
     * status disabled
     *
     * @var int
     */
    const STATUS_DISABLED = 0;

    /**
     * @var Url
     */
    protected $urlModel;
    /**
     * cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'wanexo_mlayer_banner';

    /**
     * cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'wanexo_mlayer_banner';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wanexo_mlayer_banner';

    /**
     * filter model
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * @var IsActive
     */
    protected $statusList;


	
    public function __construct(
        FilterManager $filter,
        Url $urlModel,
        IsActive $statusList,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->filter                    = $filter;
        $this->urlModel                  = $urlModel;
        $this->statusList                = $statusList;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Wanexo\Mlayer\Model\ResourceModel\Banner');
    }

    /**
     * Check if banner url key exists
     * return banner id if banner exists
     *
     * @param string $urlKey
     * @param int $storeId
     * @return int
     */
    public function checkUrlKey($urlKey, $storeId)
    {
        return $this->_getResource()->checkUrlKey($urlKey, $storeId);
    }

    /**
     * Prepare banner's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return $this->statusList->getOptions();
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get default banner values
     * @return array
     */
    public function getDefaultValues()
    {
        return [
            'is_active' => self::STATUS_ENABLED
        ];
    }

    /**
     * sanitize the url key
     *
     * @param $string
     * @return string
     */
    public function formatUrlKey($string)
    {
        return $this->filter->translitUrl($string);
    }

    /**
     * @return mixed
     */
    public function getBannerUrl()
    {
        return $this->urlModel->getBannerUrl($this);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getIsActive();
    }
}
