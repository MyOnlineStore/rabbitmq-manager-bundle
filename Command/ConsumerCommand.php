<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Command;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsumerCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->addArgument('event', InputArgument::REQUIRED)
            ->addOption('callback', null, InputOption::VALUE_REQUIRED, 'Callback service name')
            ->setName('rabbitmq-manager:consumer')
            ->setDescription('
This console command can only be used in combination with the rabbitmq-cli-consumer.
https://github.com/ricbra/rabbitmq-cli-consumer
');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = json_decode(gzuncompress(base64_decode($input->getArgument('event'))), true);

        /** @var ConsumerInterface $service */
        $service = $this->getContainer()->get($input->getOption('callback'));

        $service->execute(
            new AMQPMessage(
                $data['body'],
                $data['properties']
            )
        );
    }
}
