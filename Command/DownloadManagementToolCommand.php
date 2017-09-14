<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class DownloadManagementToolCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'filename',
                InputArgument::OPTIONAL,
                'Filename, for path see: rabbit_mq_manager.path',
                'rabbitmqadmin'
            )
            ->addOption('hostname', null, InputOption::VALUE_REQUIRED, 'Hostname', 'localhost')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Port', 15672)
            ->setName('rabbitmq-manager:download:management-tool')
            ->setDescription('Download rabbitmqadmin management tool');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('myonlinestore_rabbitmq_manager.http_client');
        $filesystem = $this->getContainer()->get('myonlinestore_rabbitmq_manager.filesystem');

        $request = $client->createRequest(
            'GET',
            sprintf(
                'http://%s:%s/cli/rabbitmqadmin',
                $input->getOption('hostname'),
                $input->getOption('port')
            )
        );

        $filesystem->put(
            $input->getArgument('filename'),
            $client->send($request)->getBody()->getContents()
        );

        $output->writeln(
            sprintf(
                'Saved to <comment>%s/%s</comment>',
                realpath($this->getContainer()->getParameter('mos_rabbitmq_cli_consumer.path')),
                $input->getArgument('filename')
            )
        );
    }
}
