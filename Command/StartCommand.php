<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Command;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Exception\Supervisor\SupervisorAlreadyRunningException;
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
        if ($input->getOption('generate')) {
            $this->getContainer()->get('myonlinestore_rabbitmq_manager.config_generator')->generate();
        }

        try {
            $this->getContainer()->get('myonlinestore_rabbitmq_manager.supervisor')->start();
            $output->writeln('Supervisord is started');
        } catch (SupervisorAlreadyRunningException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
        }
    }
}
