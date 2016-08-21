<?php

namespace CSVObjects\CSVObjectsBundle\Tests\Import;

use CSVObjects\CSVObjectsBundle\Tests\Objects\School;
use CSVObjects\CSVObjectsBundle\Tests\Objects\SchoolRepository;
use CSVObjects\CSVObjectsBundle\Tests\Objects\Student;
use CSVObjects\CSVObjectsBundle\Tests\Objects\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class ImportManagerTest extends KernelTestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    protected function setUp()
    {
        self::bootKernel();

        $this->container = self::$kernel->getContainer();

        $stJohns    = new School('St John\'s School');
        $lighthouse = new School('Lighthouse Academy');

        SchoolRepository::addSchool($stJohns);
        SchoolRepository::addSchool($lighthouse);

        StudentRepository::addStudent(new Student($stJohns, 1));
        StudentRepository::addStudent(new Student($stJohns, 2));
        StudentRepository::addStudent(new Student($lighthouse, 1));
    }

    public function testFruitsSimple()
    {
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-basic.yml'));
        $file       = __DIR__ . '/CSVs/fruits-basic.csv';
        $fruits     = $this->container->get('csv_objects.import_manager')->import($definition, $file);

        $this->assertCount(3, $fruits);
    }

    public function testConfigClassesDefinition()
    {
        $definition = Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/school-results-config.yml'));
        $file       = __DIR__ . '/CSVs/school-results.csv';
        $results    = $this->container->get('csv_objects.import_manager')->import($definition, $file);

        $this->assertCount(6, $results);
    }
}
