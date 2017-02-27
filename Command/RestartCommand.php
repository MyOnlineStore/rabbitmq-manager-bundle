<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RestartCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('rabbitmq-manager:restart')
            ->setDescription('restart supervisord')
            ->addOption('generate', null, InputOption::VALUE_NONE, 'Generate configuration files before starting')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $handler = $this->getContainer()->get('myonlinestore_rabbitmq_manager');

        if ($input->getOption('generate')) {
            $handler->generate();
        }

        $handler->stop();
        $handler->start();
    }
}
