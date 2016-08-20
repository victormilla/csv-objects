<?php

namespace CSVObjects\ImportBundle\Tests\Objects;

class Subject
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
