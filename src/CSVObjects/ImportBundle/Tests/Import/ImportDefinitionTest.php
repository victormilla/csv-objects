<?php

namespace CSVObjects\ImportBundle\Tests\Import;

use CSVObjects\ImportBundle\Import\ImportDefinition;
use CSVObjects\ImportBundle\Tests\Objects\Fruit;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

class ImportDefinitionTest extends KernelTestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testAtLeastOneColumn()
    {
        new ImportDefinition([]);
    }

    public function testOneColumn()
    {
        new ImportDefinition(['columns' => ['a' => ['a' => 'a']], 'classes' => ['a' => 'stdClass']]);
    }

    /**
     * @expectedException \LogicException
     */
    public function testOneColumnButNull()
    {
        new ImportDefinition(['columns' => ['a' => null], 'classes' => ['a' => 'stdClass']]);
    }

    public function testFruitsBasic()
    {
        $importDefinition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-basic.yml')));

        $this->assertEquals('Fruits definition', $importDefinition->getName());
        $this->assertInstanceOf(Fruit::class, $importDefinition->getClasses()['fruit']->procure('test'));
    }

    public function testColumnCopy()
    {
        $importDefinition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-full.yml')));

        $this->assertContains('Origin - City', $importDefinition->getColumnNames());
    }
}
