<?php

namespace CSVObjects\CSVObjectsBundle\Tests\Objects;

class StudentRepository
{
    /**
     * @var Student[][]
     */
    private static $studentsById;

    /**
     * @param Student $student
     */
    public static function addStudent(Student $student)
    {
        self::$studentsById[$student->getSchool()->getName()][$student->getId()] = $student;
    }

    /**
     * @param School $school
     * @param string $name
     *
     * @return Student|null
     */
    public static function findStudentById(School $school, $name)
    {
        return isset(self::$studentsById[$school->getName()][$name])
            ? self::$studentsById[$school->getName()][$name]
            : null;
    }

    /**
     * @param string $schoolName
     *
     * @return Student[]
     */
    public static function getAllStudentsInSchool($schoolName)
    {
        return isset(self::$studentsById[$schoolName])
            ? self::$studentsById[$schoolName]
            : array();
    }
}
