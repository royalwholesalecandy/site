<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Controller\Adminhtml\Segment;

class Edit extends \Amasty\Segments\Controller\Adminhtml\Segment
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $segmentId = $this->getRequest()->getParam(self::SEGMENT_PARAM_URL_KEY);

        try {
            if ($segmentModel = $this->segmentRepository->get($segmentId)) {
                $this->initCurrentSegment($segmentModel);
            } else {
                $this->messageManager->addErrorMessage(__(sprintf('Segment with ID: %s not found. ', $segmentId)));

                return $this->_redirect('amastysegments/*/');
            }

            $this->_initAction();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(
                ($segmentId ? $segmentModel->getName() : __('New Segment'))
            );
            $this->_view->renderLayout();

            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $this->_redirect('amastysegments/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the segment data. Please review the error log.')
            );

            return $this->_redirect('amastysegments/*/');
        }

        $this->messageManager->addErrorMessage(__('Somenthing went wrong!'));

        return $this->_redirect('amastysegments/*/');
    }
}
