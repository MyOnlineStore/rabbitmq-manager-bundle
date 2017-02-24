<?php

namespace MyOnlineStore\RabbitMqManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StopCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('rabbitmq-manager:stop')
            ->setDescription('stop supervisord')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $handler = $this->getContainer()->get('phobetor_rabbitmq_supervisor');
        $handler->stop();
    }
}
