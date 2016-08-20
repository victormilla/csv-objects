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
                $this->columns[$columnName] = $this->parseColumnDefinition($columnDefinition);
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
     * @param array $definition
     *
     * @return mixed
     */
    private function parseColumnDefinition(array $definition)
    {
        // TODO

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
}
