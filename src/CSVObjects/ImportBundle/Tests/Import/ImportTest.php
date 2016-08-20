<?php

namespace CSVObjects\ImportBundle\Tests\Import;

use CSVObjects\ImportBundle\Import\CSVImport;
use CSVObjects\ImportBundle\Import\ImportDefinition;
use CSVObjects\ImportBundle\Tests\Objects\Fruit;
use CSVObjects\ImportBundle\Tests\Objects\School;
use CSVObjects\ImportBundle\Tests\Objects\Student;
use CSVObjects\ImportBundle\Tests\Objects\Subject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

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

    public function testFruitsSimple()
    {
        $definition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-basic.yml')));
        $file       = __DIR__ . '/CSVs/fruits-basic.csv';
        $fruits     = CSVImport::import($definition, $file);

        /** @var Fruit[] $fruits */

        $this->assertCount(3, $fruits);

        foreach ($fruits as $fruit) {
            $this->assertInstanceOf('CSVObjects\ImportBundle\Tests\Objects\Fruit', $fruit);
        }

        $apple     = $fruits[0];
        $pineapple = $fruits[1];
        $banana    = $fruits[2];

        $this->assertEquals('Apple', $apple->getName());
        $this->assertEquals('Pineapple', $pineapple->getName());
        $this->assertEquals('Banana', $banana->getName());
    }

    public function testFruitsFull()
    {
        $definition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml')));
        $file       = __DIR__ . '/CSVs/fruits-full.csv';
        $fruits     = CSVImport::import($definition, $file);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValueNotAsExpected()
    {
        $definition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml')));
        $file       = __DIR__ . '/CSVs/fruits-full-wrong-expect.csv';

        CSVImport::import($definition, $file);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValueNotInAllowedValues()
    {
        $definition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml')));
        $file       = __DIR__ . '/CSVs/fruits-full-wrong-validate.csv';

        CSVImport::import($definition, $file);
    }
}
