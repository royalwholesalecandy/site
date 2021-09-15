<?php

namespace TemplateMonster\PromoBanner\Block\Widget;


use Magento\Widget\Block\BlockInterface;
use Magento\Cms\Block\Widget\Block;

/**
 * Class PromoBanner
 *
 * @package TemplateMonster\PromoBanner\Block\Widget
 */
class PromoBanner extends Block implements BlockInterface
{

    /**
     * Time format
     */
    const TIME_FORMAT = 'Y/m/d H:i:s';

    /**
     * Image helper
     */
    protected $_imageHelper;

    /**
     * Date
     */
    protected $_dateTime;

    /**
     * @param \Magento\Cms\Helper\Wysiwyg\Images $imageHelper,
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Helper\Wysiwyg\Images $imageHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        array $data = []
    ) {
        parent::__construct($context, $filterProvider, $blockFactory, $data);
        $this->_imageHelper = $imageHelper;
        $this->_dateTime = $dateTime;
    }

    /**
     * Enable/Disable Promo Banner
     *
     * @return bool
     */
    protected function _isEnabled()
    {
        return $this->getStatus();
    }

    /**
     * Get date timestamp
     *
     * @param time
     *
     * @return time
     */
    private function getDateTimeStamp($time)
    {
        return $this->_dateTime->timestamp($time);
    }

    /**
     * Get Start date
     *
     * @return date
     */
    public function getStartDate()
    {
        return $this->_dateTime->date(self::TIME_FORMAT, $this->getDateTimeStamp($this->getFromTime()));
    }

    /**
     * Get End date
     *
     * @return date
     */
    public function getEndDate()
    {
        return $this->_dateTime->date(self::TIME_FORMAT, $this->getDateTimeStamp($this->getToTime()));
    }

    /**
     * Get current date
     *
     * @return date
     */
    public function getCurrentDate()
    {
        return $this->_dateTime->date(self::TIME_FORMAT, $this->getDateTimeStamp(time() + $this->_dateTime->getGmtOffset()));
    }

    /**
     * Get current date
     *
     * @return string
     */
    public function getBannerImage()
    {
        return $this->_imageHelper->getImageHtmlDeclaration($this->getImageUrl());
    }

    public function filterContent($data)
    {
        return $this->_filterProvider->getBlockFilter()->filter($data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $currentDate = $this->getCurrentDate();
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        $showBanner = ($this->_isEnabled() && $currentDate >= $startDate && $currentDate <= $endDate);

        return $showBanner ? parent::_toHtml() : '';
    }
}