<?php

namespace CSVObjects\CSVObjectsBundle\Import;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

class ImportManager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string[]
     */
    private $configClasses = array();

    /**
     * @var CSVData
     */
    private $data;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string[] $configClasses
     */
    public function setConfigClasses(array $configClasses)
    {
        $this->configClasses = $configClasses;
    }

    /**
     * @param array  $definition
     * @param string $filename
     * @param bool   $rememberData Optionally remember it for debugging purposes
     *
     * @return object[]
     */
    public function import(array $definition, $filename, $rememberData = false)
    {
        // Join the definition classes with the already known classes from config.yml
        if (!isset($definition['classes'])) {
            $definition['classes'] = array();
        }

        $definition['classes'] = array_merge($definition['classes'], $this->configClasses);

        $import = new CSVImport(new ImportDefinition($definition));
        $file   = new File($filename);

        $import->setContainer($this->container);

        $results = $import->extractResultsFromFile($file, $rememberData);

        if ($rememberData) {
            $this->data = $import->getData();
        }

        return $results;
    }

    /**
     * @return CSVData
     */
    public function getData()
    {
        return $this->data;
    }
}
