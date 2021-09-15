<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Block\Adminhtml\Segment\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Amasty\Segments\Block\Adminhtml\Segment\Edit\GenericButton;

class RefreshButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->canRender('refresh_segment_data')) {
            $data = [
                'class' => 'save',
                'label' => __('Refresh Segment Data'),
                'on_click' => 'setLocation(\'' . $this->getRefreshUrl() . '\')',
                'sort_order' => 90,
            ];
        }

        return $data;
    }

    /**
     * Return URL for refresh input element 'path' in form
     *
     * @return string
     */
    public function getRefreshUrl()
    {
        return $this->getUrl(
            'amastysegments/*/refresh',
            [
                \Amasty\Segments\Controller\Adminhtml\Segment::SEGMENT_PARAM_URL_KEY => $this->getSegmentId()
            ]
        );
    }

    /**
     * @param string $name
     * @return int|null
     */
    public function canRender($name)
    {
        return $this->getSegmentId();
    }
}
