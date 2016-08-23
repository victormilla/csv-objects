<?php

namespace CSVObjects\CSVObjectsBundle\Tests\Objects;

class Contract
{
    /**
     * @var int
     */
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param int $id
     *
     * @return Contract
     */
    public static function getContractFromId($id)
    {
        return new Contract($id);
    }
}
