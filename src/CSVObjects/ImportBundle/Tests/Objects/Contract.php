<?php

namespace CSVObjects\ImportBundle\Tests\Objects;

class Contract
{
    /**
     * @var int
     */
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @param int $id
     *
     * @return Contract
     */
    public static function getContractFromId(int $id)
    {
        return new Contract($id);
    }
}
