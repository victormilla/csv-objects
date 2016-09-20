<?php

namespace CSVObjects\CSVObjectsBundle\Tests\Import;

use CSVObjects\CSVObjectsBundle\Import\CSVImport;
use CSVObjects\CSVObjectsBundle\Tests\Objects\Contract;
use CSVObjects\CSVObjectsBundle\Tests\Objects\Fruit;
use CSVObjects\CSVObjectsBundle\Tests\Objects\Result;
use CSVObjects\CSVObjectsBundle\Tests\Objects\School;
use CSVObjects\CSVObjectsBundle\Tests\Objects\Student;
use CSVObjects\CSVObjectsBundle\Tests\StaticRepositories\StaticSchoolRepository;
use CSVObjects\CSVObjectsBundle\Tests\StaticRepositories\StaticStudentRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

class CSVImportTest extends KernelTestCase
{
    protected function setUp()
    {
        $stJohns    = new School('St John\'s School');
        $lighthouse = new School('Lighthouse Academy');

        StaticSchoolRepository::addSchool($stJohns);
        StaticSchoolRepository::addSchool($lighthouse);

        StaticStudentRepository::addStudent(new Student($stJohns, 1));
        StaticStudentRepository::addStudent(new Student($stJohns, 2));
        StaticStudentRepository::addStudent(new Student($lighthouse, 1));

        date_default_timezone_set('Europe/London');
    }

    public function testEnvironment()
    {
        $this->assertCount(2, StaticSchoolRepository::getAllSchools(), 'There were two schools expected');
        $this->assertCount(2, StaticStudentRepository::getAllStudentsInSchool('St John\'s School'), 'There were two students expected in St John\'s');
        $this->assertCount(1, StaticStudentRepository::getAllStudentsInSchool('Lighthouse Academy'), 'There was one student expected in Farewell');
    }

    public function testFruitsSimple()
    {
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-basic.yml'));
        $file       = __DIR__ . '/CSVs/fruits-basic.csv';
        $fruits     = CSVImport::import($definition, $file)->getResults();

        /** @var Fruit[] $fruits */

        $this->assertCount(3, $fruits);

        foreach ($fruits as $fruit) {
            $this->assertInstanceOf(Fruit::class, $fruit);
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
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml'));
        $file       = __DIR__ . '/CSVs/fruits-full.csv';
        $fruits     = CSVImport::import($definition, $file)->getResults();

        /** @var Fruit[] $fruits */

        $this->assertCount(3, $fruits);

        foreach ($fruits as $fruit) {
            $this->assertInstanceOf(Fruit::class, $fruit);
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

    public function testXlsxFile()
    {
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml'));
        $file       = __DIR__ . '/CSVs/fruits-full.xlsx';
        $fruits     = CSVImport::import($definition, $file)->getResults();

        /** @var Fruit[] $fruits */

        $this->assertCount(3, $fruits);

        foreach ($fruits as $fruit) {
            $this->assertInstanceOf(Fruit::class, $fruit);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValueNotAsExpected()
    {
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml'));
        $file       = __DIR__ . '/CSVs/fruits-full-wrong-expect.csv';

        CSVImport::import($definition, $file);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValueNotInAllowedValues()
    {
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml'));
        $file       = __DIR__ . '/CSVs/fruits-full-wrong-validate.csv';

        CSVImport::import($definition, $file);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDateValidation()
    {
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml'));
        $file       = __DIR__ . '/CSVs/fruits-full-wrong-validate-date.csv';

        CSVImport::import($definition, $file)->getResults();
    }

    public function testUnmappedValue()
    {
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml'));
        $file       = __DIR__ . '/CSVs/fruits-full-unmapped-value.csv';
        $banana     = CSVImport::import($definition, $file)->getResults()[2];

        /** @var Fruit $banana */

        $this->assertNull($banana->getOriginCountry());
    }

    public function testColumnMapping()
    {
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml'));
        $file       = __DIR__ . '/CSVs/fruits-full.csv';
        $fruits     = CSVImport::import($definition, $file)->getResults();

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
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml'));
        $file       = __DIR__ . '/CSVs/fruits-full.csv';
        $fruits     = CSVImport::import($definition, $file)->getResults();

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
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml'));
        $file       = __DIR__ . '/CSVs/fruits-full.csv';
        $fruits     = CSVImport::import($definition, $file)->getResults();

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
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml'));
        $file       = __DIR__ . '/CSVs/fruits-full.csv';
        $fruits     = CSVImport::import($definition, $file)->getResults();

        /** @var Fruit[] $fruits */

        $apple     = $fruits[0];
        $pineapple = $fruits[1];
        $banana    = $fruits[2];

        $this->assertEquals('2015-02-11', $apple->getExpiryDate());
        $this->assertEquals('2016-09-16', $pineapple->getExpiryDate());
        $this->assertEquals('2016-09-08', $banana->getExpiryDate());
    }

    public function testMappedObject()
    {
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml'));
        $file       = __DIR__ . '/CSVs/fruits-full.csv';
        $fruits     = CSVImport::import($definition, $file)->getResults();

        /** @var Fruit[] $fruits */

        $apple     = $fruits[0];
        $pineapple = $fruits[1];
        $banana    = $fruits[2];

        $this->assertInstanceOf(Contract::class, $apple->getContract());
        $this->assertInstanceOf(Contract::class, $pineapple->getContract());
        $this->assertInstanceOf(Contract::class, $banana->getContract());
    }

    public function testStudentResults()
    {
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/school-results.yml'));
        $file       = __DIR__ . '/CSVs/school-results.csv';
        $results    = CSVImport::import($definition, $file)->getResults();

        /** @var Result[] $results */

        $this->assertCount(6, $results);

        $this->assertEquals('St John\'s School', $results[1]->getStudent()->getSchool()->getName());
        $this->assertEquals('1', $results[1]->getStudent()->getId());
        $this->assertEquals('MA', $results[1]->getSubjectCode());
        $this->assertEquals('6b', $results[1]->getGrade());

        $this->assertEquals('St John\'s School', $results[3]->getStudent()->getSchool()->getName());
        $this->assertEquals('2', $results[3]->getStudent()->getId());
        $this->assertEquals('MA', $results[3]->getSubjectCode());
        $this->assertEquals('4c', $results[3]->getGrade());

        $this->assertEquals('Lighthouse Academy', $results[4]->getStudent()->getSchool()->getName());
        $this->assertEquals('1', $results[4]->getStudent()->getId());
        $this->assertEquals('EN', $results[4]->getSubjectCode());
        $this->assertEquals('7a', $results[4]->getGrade());
    }

    public function testRawDataWhenRemembered()
    {
        $definition      = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-basic.yml'));
        $file            = __DIR__ . '/CSVs/fruits-basic.csv';
        $importedResults = CSVImport::import($definition, $file, true);

        $expected = array(
            array("Name", "Weight"),
            array("Apple", "2"),
            array("Pineapple", "3"),
            array("Banana", "1")
        );

        $this->assertEquals($expected, $importedResults->getRawData());
    }

    public function testRawDataLineOfResult()
    {
        $definition      = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/school-results.yml'));
        $file            = __DIR__ . '/CSVs/school-results.csv';
        $importedResults = CSVImport::import($definition, $file, true);

        $this->assertEquals("St John's School, 1, 6a, 6b", $importedResults->getRawDataForResult(0, true));
        $this->assertEquals(array("St John's School", "1", "6a", "6b"), $importedResults->getRawDataForResult(1));
        $this->assertEquals("Lighthouse Academy, 1, 7a, 3b", $importedResults->getRawDataForResult(4, true));
        $this->assertEquals("", $importedResults->getRawDataForResult(6, true));
        $this->assertEquals(array(), $importedResults->getRawDataForResult(6));
    }

    public function testRawDataNumberOfLine()
    {
        $definition      = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/school-results.yml'));
        $file            = __DIR__ . '/CSVs/school-results.csv';
        $importedResults = CSVImport::import($definition, $file, true);

        $this->assertEquals(1, $importedResults->getLineOfResult(0));
        $this->assertEquals(1, $importedResults->getLineOfResult(1));
        $this->assertEquals(3, $importedResults->getLineOfResult(4));
        $this->assertEquals(null, $importedResults->getLineOfResult(6));
    }

    public function testRawDataIsNullWhenNotRemembered()
    {
        $definition      = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/school-results.yml'));
        $file            = __DIR__ . '/CSVs/school-results.csv';
        $importedResults = CSVImport::import($definition, $file);

        $this->assertNull($importedResults->getRawData());
        $this->assertEquals(1, $importedResults->getLineOfResult(0));
        $this->assertEquals(1, $importedResults->getLineOfResult(1));
        $this->assertEquals(3, $importedResults->getLineOfResult(4));
        $this->assertEquals(null, $importedResults->getLineOfResult(6));
        $this->assertEquals("", $importedResults->getRawDataForResult(0, true));
        $this->assertEquals(array(), $importedResults->getRawDataForResult(0));
    }
}
