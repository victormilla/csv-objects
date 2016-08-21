<?php

namespace CSVObjects\ImportBundle\Tests\Objects;

class Student
{
    /**
     * @var School
     */
    private $school;

    /**
     * @var int
     */
    private $studentId;

    public function __construct(School $school, int $studentId)
    {
        $this->school    = $school;
        $this->studentId = $studentId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->studentId;
    }

    /**
     * @return School
     */
    public function getSchool()
    {
        return $this->school;
    }
}
