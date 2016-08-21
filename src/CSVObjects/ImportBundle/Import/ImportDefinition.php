<?php

namespace CSVObjects\ImportBundle\Import;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportDefinition
{
    const COLUMN_DELIMITER = '#';

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

    /**
     * @var callable[][]
     */
    private $validations = array();

    /**
     * @var string[][]
     */
    private $maps = array();

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
        // Expect
        if (isset($definition['expect'])) {
            $expectedValue = $definition['expect'];

            $this->validations[$columnName][] = function ($row) use ($columnName, $expectedValue) {
                if ($row[$columnName] !== $expectedValue) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'The column \'%s\' is expected to always have the value \'%s\'. However, there is a row there the value is \'%s\'',
                            $columnName,
                            $expectedValue,
                            $row[$columnName]
                        )
                    );
                }
            };

            unset($definition['expect']);
        }

        // Validate
        if (isset($definition['validate'])) {
            $validValues = $definition['validate'];

            $this->validations[$columnName][] = function ($row) use ($columnName, $validValues) {
                if (!in_array($row[$columnName], $validValues, true)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'The column \'%s\' is expected to have one of these values: (%s). However, there is a row there the value is \'%s\' which is not on the list',
                            $columnName,
                            json_encode($validValues),
                            $row[$columnName]
                        )
                    );
                }
            };

            unset($definition['validate']);
        }

        // Map
        if (isset($definition['map'])) {
            $this->maps[$columnName] = $definition['map'];

            unset($definition['map']);
        }

        // Class
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

        // Process row validation rules
        foreach ($this->validations as $validations) {
            foreach ($validations as $validation) {
                $validation($row);
            }
        }

        // Replace values by their aliases
        foreach ($this->maps as $columnName => $map) {
            $row[$columnName] = isset($map[$row[$columnName]])
                ? $map[$row[$columnName]]
                : null;
        }

        foreach ($this->returnDataColumns as $columnName => $arguments) {
            $args = [];

            if (!is_array($arguments)) {
                $arguments = array($arguments);
            }

            foreach ($arguments as $argument) {
                $args[] = $this->makeArgumentReplacements($argument, $row);;
            }

            $r[] = $args;
        }

        return $r;
    }

    /**
     * @param string $argument
     * @param array  $row
     *
     * @return string
     */
    private function makeArgumentReplacements(string $argument, array $row)
    {
        $numberMatches = preg_match_all(
            sprintf('/%s[^%s]+?%s/', self::COLUMN_DELIMITER, self::COLUMN_DELIMITER, self::COLUMN_DELIMITER),
            $argument,
            $matches
        );

        if (false === $numberMatches) {
            throw new \RuntimeException('Something went wrong when finding the replacements of the argument');
        }

        foreach ($matches[0] as $match) {
            $search       = substr($match, 1, -1);
            $originalType = gettype($row[$search]);
            $argument = str_replace(sprintf('%s%s%s', self::COLUMN_DELIMITER, $search, self::COLUMN_DELIMITER), $row[$search], $argument);

            // Restore the original data type
            switch ($originalType) {
                case 'string':
                    break;
                case 'boolean':
                    $argument = boolval($argument);
                    break;
                case 'integer':
                    $argument = intval($argument);
                    break;
                case 'double':
                    $argument = floatval($argument);
                    break;
                case 'NULL':
                    $argument = null;
                    break;
                default:
                    throw new \LogicException('Cannot process values of type ' . $originalType);
            }
        }

        return $argument;
    }
}
