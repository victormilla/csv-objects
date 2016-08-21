<?php

namespace CSVObjects\CSVObjectsBundle\Tests\Objects;

class Result
{
    /**
     * @var Student
     */
    private $student;

    /**
     * @var string
     */
    private $subjectCode;

    /**
     * @var string
     */
    private $grade;

    public function __construct(Student $student, string $subjectCode, string $grade)
    {
        $this->student     = $student;
        $this->subjectCode = $subjectCode;
        $this->grade       = $grade;
    }

    /**
     * @return Student
     */
    public function getStudent(): Student
    {
        return $this->student;
    }

    /**
     * @return string
     */
    public function getSubjectCode(): string
    {
        return $this->subjectCode;
    }

    /**
     * @return string
     */
    public function getGrade(): string
    {
        return $this->grade;
    }
}
