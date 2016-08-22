<?php

namespace CSVObjects\CSVObjectsBundle\ObjectProcurer;

use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ObjectProcurer
{
    const USE_REFLECTION_CLASS = 1;
    const MAKE_STATIC_CALL = 2;
    const USE_SERVICE_CONTAINER = 3;

    /**
     * @var int
     */
    private $mode;

    /**
     * @var ReflectionClass|string[]
     */
    private $class;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param string|array $classReference If it is a string, means that needs to use that class constructor; otherwise it makes a static call
     */
    public function __construct($classReference)
    {
        if (is_string($classReference)) {
            $this->class = new ReflectionClass($classReference);
            $this->mode  = self::USE_REFLECTION_CLASS;
        } else {
            $this->class = $classReference;
            $this->mode  = substr($classReference[0], 0, 1) === '@'
                ? self::USE_SERVICE_CONTAINER
                : self::MAKE_STATIC_CALL;
        }
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
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

        switch ($this->mode) {
            case self::USE_REFLECTION_CLASS:
                return $this->class->newInstanceArgs($arguments);
            case self::MAKE_STATIC_CALL:
                return call_user_func_array($this->class, $arguments);
            case self::USE_SERVICE_CONTAINER:
                if (null === $this->container) {
                    throw new \InvalidArgumentException('To use the ObjectProcurer with a service, you need to call the \'setContainer\' method first');
                }

                return call_user_func_array(
                    array(
                        $this->container->get(substr($this->class[0], 1)),
                        $this->class[1]
                    ),
                    $arguments
                );
        }

        return null;
    }
}
