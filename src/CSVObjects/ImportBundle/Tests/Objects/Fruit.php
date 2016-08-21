<?php

namespace CSVObjects\ImportBundle\Tests\Objects;

class Fruit
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $colour;

    /**
     * @var string|null
     */
    private $originCountry;

    public function __construct(string $name, string $colour = null, string $originCountry = null)
    {
        $this->name          = $name;
        $this->colour        = $colour;
        $this->originCountry = $originCountry;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getColour(): string
    {
        return $this->colour;
    }

    /**
     * @return null|string
     */
    public function getOriginCountry()
    {
        return $this->originCountry;
    }
}
