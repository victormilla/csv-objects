<?php

namespace CSVObjects\ImportBundle\Tests\Objects;

class Result
{
    /**
     * @var Student
     */
    private $student;

    /**
     * @var Subject
     */
    private $subject;

    /**
     * @var string
     */
    private $grade;

    public function __construct(Student $student, Subject $subject, string $grade)
    {
        $this->student = $student;
        $this->subject = $subject;
        $this->grade   = $grade;
    }
}
