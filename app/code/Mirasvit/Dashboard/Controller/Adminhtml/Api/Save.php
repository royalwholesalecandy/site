<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-dashboard
 * @version   1.2.35
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\Controller\Adminhtml\Api;

use Magento\Backend\App\Action\Context;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Model\Block;
use Mirasvit\Dashboard\Repository\BoardRepository;
use Mirasvit\Report\Api\Service\CastingServiceInterface;
use Mirasvit\Report\Controller\Adminhtml\Api\AbstractApi;

class Save extends AbstractApi
{
    private $boardRepository;

    private $castingService;

    public function __construct(
        BoardRepository $boardRepository,
        CastingServiceInterface $castingService,
        Context $context
    ) {
        $this->boardRepository = $boardRepository;
        $this->castingService  = $castingService;

        parent::__construct($context);
    }

    public function execute()
    {
        $board = $this->castingService->toUnderscore($this->getRequest()->getParam('board'));

        $identifier = $board[BoardInterface::IDENTIFIER];

        $model = $this->boardRepository->getByIdentifier($identifier);

        if (!$model) {
            $model = $this->boardRepository->create();
        }

        $model->setTitle($board[BoardInterface::TITLE])
            ->setIdentifier($board[BoardInterface::IDENTIFIER])
            ->setType($board[BoardInterface::TYPE])
            ->setIsDefault($board[BoardInterface::IS_DEFAULT])
            ->setIsMobileEnabled($board[BoardInterface::IS_MOBILE_ENABLED])
            ->setMobileToken($board[BoardInterface::MOBILE_TOKEN]);

        $blocks = [];
        foreach ($board[BoardInterface::BLOCKS] as $item) {
            $blocks[] = new Block($item);
        }

        $model->setBlocks($blocks);

        $this->boardRepository->save($model);

        /** @var \Magento\Framework\App\Response\Http $jsonResponse */
        $jsonResponse = $this->getResponse();
        $jsonResponse->representJson(\Zend_Json::encode([
            'success' => true,
            'message' => 'Board was saved.',
        ]));
    }
}