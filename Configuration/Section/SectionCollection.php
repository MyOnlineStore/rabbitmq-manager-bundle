<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Section;

use Supervisor\Configuration\Section;

final class SectionCollection
{
    /**
     * @var Section[]
     */
    private $sections;

    /**
     * @param Section[] $sections
     */
    public function __construct(array $sections = [])
    {
        foreach ($sections as $section) {
            $this->addSection($section);
        }
    }

    /**
     * @param Section $section
     */
    public function addSection(Section $section)
    {
        $this->sections[$section->getName()] = $section->getProperties();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->sections;
    }
}
