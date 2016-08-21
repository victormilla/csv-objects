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

    /**
     * @var string|null
     */
    private $class;

    /**
     * @var string|null
     */
    private $expiryDate;

    public function __construct(
        string $name,
        string $colour = null,
        string $originCountry = null,
        string $originCity = null,
        string $class = null,
        string $expiryDate = null
    ) {
        $this->name          = $name;
        $this->colour        = $colour;
        $this->originCountry = $originCountry;
        $this->originCity    = $originCity;
        $this->class         = $class;
        $this->expiryDate    = $expiryDate;
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

    /**
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string|null
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }
}
