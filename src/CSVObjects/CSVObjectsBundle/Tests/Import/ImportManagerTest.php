<?php

namespace CSVObjects\CSVObjectsBundle\Tests\Import;

use CSVObjects\CSVObjectsBundle\Tests\Objects\School;
use CSVObjects\CSVObjectsBundle\Tests\Objects\Student;
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

        $schoolRepository = $this->container->get('csv_objects.tests.school_repository');
        $studentRepository = $this->container->get('csv_objects.tests.student_repository');

        $schoolRepository->addSchool($stJohns);
        $schoolRepository->addSchool($lighthouse);

        $studentRepository->addStudent(new Student($stJohns, 1));
        $studentRepository->addStudent(new Student($stJohns, 2));
        $studentRepository->addStudent(new Student($lighthouse, 1));
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
