<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('rabbitmq-manager:start')
            ->setDescription('start supervisord')
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

        $handler->start();
    }
}
