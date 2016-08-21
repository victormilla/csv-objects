<?php

namespace CSVObjects\ImportBundle\Import;

use CSVObjects\ImportBundle\ObjectProcurer\ObjectProcurer;
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
     * @var ObjectProcurer[]
     */
    private $classes;

    /**
     * @var string
     */
    private $returnClass;

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

    /**
     * @var string[]
     */
    private $extracts = array();

    /**
     * @var string[]
     */
    private $dateSourceFormat = array();

    /**
     * @var string[]
     */
    private $dateFormat = array();

    /**
     * @var ObjectProcurer[]
     */
    private $objectProcurers = array();

    /**
     * @var array[]
     */
    private $objectProcurersArguments = array();

    public function __construct(array $definition)
    {
        $resolver = new OptionsResolver();

        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($definition);

        if (0 === count($this->options['columns'])) {
            throw new \InvalidArgumentException('An import definition must contain at least one column');
        }

        $classes = $this->options['classes'];

        if (0 === count($classes)) {
            throw new \InvalidArgumentException('The return class must be specified inside classes');
        }

        reset($classes);

        $this->returnClass = key($classes);

        foreach ($classes as $classAlias => $classDefinition) {
            $this->classes[$classAlias] = new ObjectProcurer($classDefinition);
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
                'classes' => [],
                'copy'    => [],
            ]
        );

        $resolver->setRequired('columns');
        $resolver->setAllowedTypes('name', ['string', 'null']);
        $resolver->setAllowedTypes('classes', ['array']);
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
            $validation = $definition['validate'];

            if (is_array($validation)) {
                $this->validations[$columnName][] = function ($row) use ($columnName, $validation) {
                    if (!in_array($row[$columnName], $validation, true)) {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'The column \'%s\' is expected to have one of these values: (%s). However, there is a row there the value is \'%s\' which is not on the list',
                                $columnName,
                                json_encode($validation),
                                $row[$columnName]
                            )
                        );
                    }
                };
            } elseif ('date' === $validation) {
                $sourceFormat = isset($definition['sourceFormat'])
                    ? $definition['sourceFormat']
                    : null;

                if (null !== $sourceFormat) {
                    $this->dateSourceFormat[$columnName] = $sourceFormat;
                }

                if (isset($definition['format'])) {
                    $this->dateFormat[$columnName] = $definition['format'];
                    $sourceFormat                  = $definition['format'];
                }

                unset($definition['sourceFormat'], $definition['format']);

                $this->validations[$columnName][] = function ($row) use ($columnName, $validation, $sourceFormat) {
                    try {
                        $date = null === $sourceFormat
                            ? new \DateTime($row[$columnName])
                            : \DateTime::createFromFormat($sourceFormat, $row[$columnName]);

                        if (false === $date) {
                            throw new \Exception();
                        }
                    } catch (\Exception $caught) {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'The column \'%s\' is expected to be a date. However, there is a row there the value is \'%s\' which is not valid (or valid in the specified format)',
                                $columnName,
                                $row[$columnName]
                            )
                        );
                    }
                };
            } else {
                throw new \InvalidArgumentException('Unknown validation rule: ' . $validation);
            }

            unset($definition['validate']);
        }

        // Map
        if (isset($definition['map'])) {
            $this->maps[$columnName] = $definition['map'];

            unset($definition['map']);
        }

        // Extract
        if (isset($definition['extract'])) {
            $this->extracts[$columnName] = $definition['extract'];

            unset($definition['extract']);
        }

        // Mapped Class
        foreach ($this->classes as $mappedClassName => $mappedClass) {
            if ($mappedClassName !== $this->returnClass && isset($definition[$mappedClassName])) {
                $this->objectProcurers[$columnName]          = $mappedClass;
                $this->objectProcurersArguments[$columnName] = is_array($definition[$mappedClassName])
                    ? $definition[$mappedClassName]
                    : array($definition[$mappedClassName]);

                unset($definition[$mappedClassName]);
            }
        }

        // Return Class
        if (isset($definition[$this->returnClass])) {
            $this->returnDataColumns[$columnName] = $definition[$this->returnClass];

            unset($definition[$this->returnClass]);
        }

        if (0 !== count($definition)) {
            throw new \InvalidArgumentException('Something is not right as there is something left on the definition that is not recognised: ' . json_encode($definition));
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
     * @return ObjectProcurer[]
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @return string
     */
    public function getReturnClass()
    {
        return $this->returnClass;
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

        // Date reformat
        foreach ($this->dateFormat as $columnName => $format) {
            try {
                $date = !isset($this->dateSourceFormat[$columnName])
                    ? new \DateTime($row[$columnName])
                    : \DateTime::createFromFormat($this->dateSourceFormat[$columnName], $row[$columnName]);

                if (false === $date || 0 < \DateTime::getLastErrors()['warning_count']) {
                    throw new \Exception();
                }

                $row[$columnName] = $date->format($format);
            } catch (\Exception $caught) {
                // Do nothing
            }
        }

        // Extract data from value
        foreach ($this->extracts as $columnName => $extract) {
            preg_match(sprintf('/%s/', $extract), $row[$columnName], $matches);

            $row[$columnName] = isset($matches[1])
                ? $matches[1]
                : null;
        }

        // Replace values by their aliases
        foreach ($this->maps as $columnName => $map) {
            $row[$columnName] = isset($map[$row[$columnName]])
                ? $map[$row[$columnName]]
                : null;
        }

        // Process row validation rules
        foreach ($this->validations as $validations) {
            foreach ($validations as $validation) {
                $validation($row);
            }
        }

        // Convert relevant columns to objects
        foreach ($this->objectProcurers as $columnName => $objectProcurer) {
            $arguments = array();

            foreach ($this->objectProcurersArguments[$columnName] as $argument) {
                $arguments[] = $this->makeArgumentReplacements($argument, $row);;
            }

            $row[$columnName] = $objectProcurer->procure($arguments);
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

            if ('object' === $originalType) {
                $argument = $row[$search];
                break;
            }

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

    /**
     * @return string[]
     */
    public function getColumnCopies()
    {
        return $this->options['copy'];
    }
}
