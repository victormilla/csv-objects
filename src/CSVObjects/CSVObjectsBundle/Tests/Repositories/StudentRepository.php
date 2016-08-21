<?php

namespace CSVObjects\CSVObjectsBundle\Tests\Repositories;

use CSVObjects\CSVObjectsBundle\Tests\Objects\School;
use CSVObjects\CSVObjectsBundle\Tests\Objects\Student;

class StudentRepository
{
    /**
     * @var Student[][]
     */
    private $studentsById;

    /**
     * @param Student $student
     */
    public function addStudent(Student $student)
    {
        $this->studentsById[$student->getSchool()->getName()][$student->getId()] = $student;
    }

    /**
     * @param School $school
     * @param string $name
     *
     * @return Student|null
     */
    public function findStudentById(School $school, $name)
    {
        return isset($this->studentsById[$school->getName()][$name])
            ? $this->studentsById[$school->getName()][$name]
            : null;
    }

    /**
     * @param string $schoolName
     *
     * @return Student[]
     */
    public function getAllStudentsInSchool($schoolName)
    {
        return isset($this->studentsById[$schoolName])
            ? $this->studentsById[$schoolName]
            : array();
    }
}
