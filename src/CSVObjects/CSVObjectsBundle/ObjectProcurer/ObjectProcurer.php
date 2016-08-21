<?php

namespace CSVObjects\CSVObjectsBundle\ObjectProcurer;

use ReflectionClass;

class ObjectProcurer
{
    /**
     * @var bool
     */
    private $useRefectionClass;

    /**
     * @var ReflectionClass|string[]
     */
    private $class;

    /**
     * @param string|array $classReference If it is a string, means that needs to use that class constructor; otherwise it makes a static call
     */
    public function __construct($classReference)
    {
        if (is_string($classReference)) {
            $this->useRefectionClass = true;
            $this->class             = new ReflectionClass($classReference);
        } else {
            $this->useRefectionClass = false;
            $this->class             = $classReference;
        }
    }

    /**
     * @param array|string $arguments
     *
     * @return object
     */
    public function procure($arguments)
    {
        if (!is_array($arguments)) {
            $arguments = array($arguments);
        }

        return $this->useRefectionClass
            ? $this->class->newInstanceArgs($arguments)
            : call_user_func_array($this->class, $arguments);
    }
}
