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

        $this->assertEquals('red', $apple->getColour());
        $this->assertEquals('yellow', $pineapple->getColour());
        $this->assertEquals('yellow', $banana->getColour());
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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDateValidation()
    {
        $definition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml')));
        $file       = __DIR__ . '/CSVs/fruits-full-wrong-validate-date.csv';

        CSVImport::import($definition, $file);
    }

    public function testUnmappedValue()
    {
        $definition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml')));
        $file       = __DIR__ . '/CSVs/fruits-full-unmapped-value.csv';
        $banana     = CSVImport::import($definition, $file)[2];

        /** @var Fruit $banana */

        $this->assertNull($banana->getOriginCountry());
    }

    public function testColumnMapping()
    {
        $definition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml')));
        $file       = __DIR__ . '/CSVs/fruits-full.csv';
        $fruits     = CSVImport::import($definition, $file);

        /** @var Fruit[] $fruits */

        $apple     = $fruits[0];
        $pineapple = $fruits[1];
        $banana    = $fruits[2];

        $this->assertEquals('UK', $apple->getOriginCountry());
        $this->assertEquals('Spain', $pineapple->getOriginCountry());
        $this->assertEquals('Spain', $banana->getOriginCountry());
    }

    public function testColumnCopy()
    {
        $definition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml')));
        $file       = __DIR__ . '/CSVs/fruits-full.csv';
        $fruits     = CSVImport::import($definition, $file);

        /** @var Fruit[] $fruits */

        $apple     = $fruits[0];
        $pineapple = $fruits[1];
        $banana    = $fruits[2];

        $this->assertEquals('Dover', $apple->getOriginCity());
        $this->assertEquals('Malaga', $pineapple->getOriginCity());
        $this->assertEquals('Granada', $banana->getOriginCity());
    }

    public function testExtract()
    {
        $definition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml')));
        $file       = __DIR__ . '/CSVs/fruits-full.csv';
        $fruits     = CSVImport::import($definition, $file);

        /** @var Fruit[] $fruits */

        $apple     = $fruits[0];
        $pineapple = $fruits[1];
        $banana    = $fruits[2];

        $this->assertEquals('A+', $apple->getClass());
        $this->assertEquals('B', $pineapple->getClass());
        $this->assertEquals('A', $banana->getClass());
    }

    public function testDateFormatting()
    {
        $definition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml')));
        $file       = __DIR__ . '/CSVs/fruits-full.csv';
        $fruits     = CSVImport::import($definition, $file);

        /** @var Fruit[] $fruits */

        $apple     = $fruits[0];
        $pineapple = $fruits[1];
        $banana    = $fruits[2];

        $this->assertEquals('2015-02-11', $apple->getExpiryDate());
        $this->assertEquals('2016-09-16', $pineapple->getExpiryDate());
        $this->assertEquals('2016-09-08', $banana->getExpiryDate());
    }
}
