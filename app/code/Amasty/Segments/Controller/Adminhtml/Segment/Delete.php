<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Controller\Adminhtml\Segment;

class Delete extends \Amasty\Segments\Controller\Adminhtml\Segment
{
    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(self::SEGMENT_PARAM_URL_KEY);

        if ($id) {
            try {
                $this->segmentRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the segment.'));

                return $this->_redirect('amastysegments/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete the segment right now. Please try again.')
                );

                return $this->_redirect('amastysegments/*/edit', [self::SEGMENT_PARAM_URL_KEY => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a segment to delete.'));

        return $this->_redirect('amastysegments/*/');
    }
}
