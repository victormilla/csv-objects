<?php

namespace CSVObjects\CSVObjectsBundle\Import;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
     *
     * @return object[]
     */
    public function import(array $definition, string $filename)
    {
        // Join the definition classes with the already known classes from config.yml
        if (!isset($definition['classes'])) {
            $definition['classes'] = array();
        }

        $definition['classes'] = array_merge($definition['classes'], $this->configClasses);

        return CSVImport::import($definition, $filename);
    }
}
