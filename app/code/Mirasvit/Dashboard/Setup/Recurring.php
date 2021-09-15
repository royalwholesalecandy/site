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



namespace Mirasvit\Dashboard\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Mirasvit\Dashboard\Api\Repository\BoardRepositoryInterface;
use Mirasvit\Dashboard\Model\Block;

class Recurring implements InstallSchemaInterface
{
    private $boardRepository;

    private $arrayManager;

    public function __construct(
        BoardRepositoryInterface $boardRepository,
        ArrayManager $arrayManager
    ) {
        $this->boardRepository = $boardRepository;
        $this->arrayManager    = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        foreach ($this->boardRepository->getCollection() as $board) {
            $blocks = $board->getBlocks();
            /** @var Block $block */
            foreach ($blocks as $key => $block) {
                $data = $block->getData();

                $this->replace($data, 'renderer', 'config/renderer');

                $this->replace($data, 'report/data', 'config/single/column');
                $this->replace($data, 'single/sparkline/isActive', 'config/single/sparkLine');

                $block->setData($data);
                $blocks[$key] = $block;
            }

            $board->setBlocks($blocks);

            $this->boardRepository->save($board);
        }

        $installer->endSetup();
    }

    /**
     * @param array  &$data
     * @param string $oldPath
     * @param string $newPath
     */
    private function replace(&$data, $oldPath, $newPath)
    {
        $oldValue = $this->arrayManager->get($oldPath, $data);
        $newValue = $this->arrayManager->get($newPath, $data);

        if ($oldValue && !$newValue) {
            $data = $this->arrayManager->set($newPath, $data, $oldValue);
        }
        $data = $this->arrayManager->remove($oldPath, $data);
    }
}
