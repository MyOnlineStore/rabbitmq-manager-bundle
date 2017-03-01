<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Configuration\Consumer\Section;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Logs;

class LogsTest extends \PHPUnit_Framework_TestCase
{
    public function testSection()
    {
        $input = [
            'info' => '/path/to/file.info',
            'error' => '/path/to/file.error',
        ];

        $section = new Logs($input);

        $this->assertEquals('logs', $section->getName());
        $this->assertEquals($input, $section->getProperties());
    }
}
