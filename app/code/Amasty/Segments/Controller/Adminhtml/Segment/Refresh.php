<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Controller\Adminhtml\Segment;

class Refresh extends \Amasty\Segments\Controller\Adminhtml\Segment
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        if ($segmentId = $this->getRequest()->getParam(self::SEGMENT_PARAM_URL_KEY)) {
            try {

                $model = $this->segmentRepository->get($segmentId);

                if ($model->getSegmentId()) {
                    $this->segmentCustomerIndexer->executeRow($model->getSegmentId());
                }

                $this->_getSession()->setPageData($model->getData());
                $this->messageManager->addSuccessMessage(__('You refresh the Segment.'));

                if ($segmentId != $model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The wrong Segment is specified.')
                    );
                }

                return $this->_redirect('amastysegments/*/edit', [self::SEGMENT_PARAM_URL_KEY => $segmentId]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam(self::SEGMENT_PARAM_URL_KEY);

                if (!empty($id)) {
                    $this->_redirect('amastysegments/*/edit', [self::SEGMENT_PARAM_URL_KEY => $id]);
                } else {
                    $this->_redirect('amastysegments/*');
                }

                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while refreshing the segment data. Please try again.')
                );

                $this->_redirect(
                    'amastysegments/*/edit',
                    [self::SEGMENT_PARAM_URL_KEY => $this->getRequest()->getParam(self::SEGMENT_PARAM_URL_KEY)]
                );

                return;
            }
        }

        $this->messageManager->addErrorMessage(__('Something went wront. We can\'t find a segment ID.'));

        return $this->_redirect('amastysegments/*/');
    }
}
