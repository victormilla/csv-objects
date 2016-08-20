<?php

namespace CSVObjects\ImportBundle\Import;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportDefinition
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var string[]
     */
    private $columnNames;

    /**
     * @var array
     */
    private $columns;

    /**
     * Known classes alias
     *
     * @var string[]
     */
    private $classes;

    /**
     * @var string[]
     */
    private $returnDataColumns = array();

    public function __construct(array $definition)
    {
        $resolver = new OptionsResolver();

        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($definition);

        if (0 === count($this->options['columns'])) {
            throw new \InvalidArgumentException('An import definition must contain at least one column');
        }

        foreach ($this->options['columns'] as $columnName => $columnDefinition) {
            $this->columnNames[] = $columnName;

            if (null !== $columnDefinition) {
                $this->columns[$columnName] = $this->parseColumnDefinition($columnName, $columnDefinition);
            }
        }

        if (0 === count($this->columns)) {
            throw new \InvalidArgumentException('There must be at least one column with an action');
        }
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'name'    => null,
                'columns' => [],
                'returns' => null,
            ]
        );

        $resolver->setRequired('columns');
        $resolver->setAllowedTypes('name', ['string', 'null']);
        $resolver->setAllowedTypes('returns', ['string', 'null']);
    }

    /**
     * @param string $columnName
     * @param array  $definition
     *
     * @return mixed
     */
    private function parseColumnDefinition(string $columnName, array $definition)
    {
        // TODO

        if (1 === count($definition)) {
            // It must be talking about objects.

            $classAlias = key($definition);
            $arguments  = end($definition);

            if (isset($this->classes[$classAlias])) {
                // TODO
            } else {
                // It is the return class

                $this->returnDataColumns[$columnName] = $arguments;
            }
        }

        return true;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->options['name'];
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->options['returns'];
    }

    /**
     * @return string[]
     */
    public function getColumnNames()
    {
        return $this->columnNames;
    }

    /**
     * @param array $row
     *
     * @return array[]
     */
    public function getArgumentsByData(array $row)
    {
        $r = array();

        foreach ($this->returnDataColumns as $columnName => $arguments) {
            $args   = [];
            $args[] = $row[$columnName];
            $r[]    = $args;
        }

        return $r;
    }
}
