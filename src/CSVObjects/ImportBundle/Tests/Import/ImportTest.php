<?php

namespace CSVObjects\ImportBundle\Tests\Import;

use CSVObjects\ImportBundle\Tests\Objects\School;
use CSVObjects\ImportBundle\Tests\Objects\Student;
use CSVObjects\ImportBundle\Tests\Objects\Subject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImportTest extends KernelTestCase
{
    /**
     * @var School[]
     */
    private $schools;

    /**
     * @var Student[][]
     */
    private $students;

    /**
     * @var Subject[]
     */
    private $subjects;

    protected function setUp()
    {
        $this->schools = [
            'st-johns'   => new School('St John\'s School'),
            'lighthouse' => new School('Lighthouse Academy'),
        ];

        $this->students = [
            'st-johns'   => [
                1 => new Student($this->schools['lighthouse'], 1),
                2 => new Student($this->schools['lighthouse'], 2),
            ],
            'lighthouse' => [
                1 => new Student($this->schools['lighthouse'], 1),
            ],
        ];

        $this->subjects = [
            'EN' => new Subject('English'),
            'MA' => new Subject('Maths'),
        ];
    }

    public function testEnvironment()
    {
        $this->assertCount(2, $this->schools, 'There were two schools expected');
        $this->assertCount(2, $this->students['st-johns'], 'There were two students expected in St John\'s');
        $this->assertCount(1, $this->students['lighthouse'], 'There was one student expected in Farewell');
        $this->assertCount(2, $this->subjects, 'There where two subjects expected');
    }
}
