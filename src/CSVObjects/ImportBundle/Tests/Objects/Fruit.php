<?php

namespace CSVObjects\ImportBundle\Tests\Objects;

class Fruit
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $colour;

    /**
     * @var string|null
     */
    private $originCountry;

    /**
     * @var string|null
     */
    private $originCity;

    public function __construct(
        string $name,
        string $colour = null,
        string $originCountry = null,
        string $originCity = null
    ) {
        $this->name          = $name;
        $this->colour        = $colour;
        $this->originCountry = $originCountry;
        $this->originCity    = $originCity;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getColour()
    {
        return $this->colour;
    }

    /**
     * @return string|null
     */
    public function getOriginCountry()
    {
        return $this->originCountry;
    }

    /**
     * @return string|null
     */
    public function getOriginCity()
    {
        return $this->originCity;
    }
}
