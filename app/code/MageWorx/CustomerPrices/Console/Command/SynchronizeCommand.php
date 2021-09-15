<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use MageWorx\CustomerPrices\Model\Synchronizer;

class SynchronizeCommand extends Command
{
    /**
     * @var Synchronizer
     */
    protected $synchronizer;

    /**
     * SynchronizeCommand constructor.
     *
     * @param Synchronizer $synchronizer
     */
    public function __construct(
        Synchronizer $synchronizer
    ) {
        $this->synchronizer = $synchronizer;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mageworx-customer-prices:synchronize');
        $this->setDescription(
            'Please use this command to manually synchronize data in case you\'ve changed prices' .
            ' but don\'t observe this on the frontend'
        );
        parent::configure();
    }

    /**
     *
     * @return boolean
     */
    protected function isEnable()
    {
        return true;
    }

    /**
     * Dispatch event
     *
     * @return array
     */
    protected function performAction()
    {
        $result = ['message' => ''];
        try {
            $this->synchronizer->synchronizeData();
            $result['message'] = $this->getSuccessMessage();
            $result['time'] = time();
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $result['time'] = false;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDisplayMessage()
    {
        return 'Synchronizing...';
    }

    /**
     * Retrieve finish notice
     *
     * @return string
     */
    protected function getSuccessMessage()
    {
        return 'Data synchronizing has been finished successfully.';
    }

    /**
     * Perform cache management action
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDisplayMessage());
        $result = $this->performAction();
        $output->writeln($result['message']);

        if ($result['time']) {
            $output->writeln('Last synchronize time is ' . $result['time']);
        }
    }
}