<?php

namespace CSVObjects\ImportBundle\Tests\Import;

use CSVObjects\ImportBundle\Import\ImportDefinition;
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
        new ImportDefinition(['columns' => ['a' => ['a: a']]]);
    }

    /**
     * @expectedException \LogicException
     */
    public function testOneColumnButNull()
    {
        new ImportDefinition(['columns' => ['a' => null]]);
    }

    public function testFruitsBasic()
    {
        $importDefinition = new ImportDefinition(Yaml::parse(file_get_contents(__DIR__ . '/ImportDefinitions/fruits-basic.yml')));

        $this->assertEquals('Fruits definition', $importDefinition->getName());
        $this->assertEquals('CSVObjects\ImportBundle\Tests\Objects\Fruit', $importDefinition->getClass());
    }
}
