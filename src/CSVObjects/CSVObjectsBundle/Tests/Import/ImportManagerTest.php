<?php

namespace CSVObjects\CSVObjectsBundle\Tests\Import;

use CSVObjects\CSVObjectsBundle\Tests\Objects\School;
use CSVObjects\CSVObjectsBundle\Tests\Objects\Student;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class ImportManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $kernel;

    private $cacheDir;


    protected function setUp()
    {
        $this->cacheDir = __DIR__.'/../../Resources/cache';
        if (file_exists($this->cacheDir)) {
            $filesystem = new Filesystem();
            $filesystem->remove($this->cacheDir);
        }
        mkdir($this->cacheDir, 0777, true);

        $this->kernel = new TestKernel('test', false);
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();

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

    public function tearDown()
    {
        if (file_exists($this->cacheDir)) {
            $filesystem = new Filesystem();
            $filesystem->remove($this->cacheDir);
        }
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
