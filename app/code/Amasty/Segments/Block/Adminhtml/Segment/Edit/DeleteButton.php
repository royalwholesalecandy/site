<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Block\Adminhtml\Segment\Edit;

use Amasty\Segments\Block\Adminhtml\Segment\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $segmentId = $this->getSegmentId();

        if ($segmentId && $this->canRender('delete')) {
            $data = [
                'label' => __('Delete Segment'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->urlBuilder->getUrl(
                        '*/*/delete',
                        [\Amasty\Segments\Controller\Adminhtml\Segment::SEGMENT_PARAM_URL_KEY => $segmentId]
                    ) . '\')',
                'sort_order' => 20,
            ];
        }

        return $data;
    }
}
