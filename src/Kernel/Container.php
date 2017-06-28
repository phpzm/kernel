<?php

namespace Simples\Kernel;

use Simples\Error\NotFoundExceptionInterface;
use Simples\Error\SimplesRunTimeError;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;

/**
 * Class Container
 * @package Simples\Kernel
 */
class Container
{
    /**
     * @var Container Instance of Container container
     */
    protected static $instance;

    /**
     * @var array List of IoC Bindings, empty array for default
     */
    protected $bindings = [];

    /**
     * Container constructor.
     *
     * Constructor is protected so people can never
     * do "new Container()"
     */
    protected function __construct()
    {
        //
    }

    /**
     * A singleton to a Container instance
     *
     * @return Container Current Container container instance
     */
    public static function instance()
    {
        // if there is not a instance yet, create a new one
        if (null === self::$instance) {
            self::$instance = new self();
        }

        // return the new or already existing instance
        return self::$instance;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $alias Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for this identifier.
     *
     * @return mixed Entry.
     */
    public function get($alias)
    {
        if (!$this->has($alias)) {
            throw new NotFoundExceptionInterface();
        }
        return $this->bindings[$alias];
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundException`.
     *
     * @param string $alias Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($alias)
    {
        return isset($this->bindings[$alias]);
    }

    /**
     * Register a class or alias into the Container.
     *
     * @param $alias
     * @param $implementation
     * @return $this
     */
    public function register($alias, $implementation)
    {
        $this->bindings[$alias] = $implementation;

        return $this;
    }

    /**
     * UnRegister a Interface/Class/Alias.
     *
     * @param $aliasOrClassName
     * @return $this
     */
    public function unRegister($aliasOrClassName)
    {
        if (array_key_exists($aliasOrClassName, $this->bindings)) {
            unset($this->bindings[$aliasOrClassName]);
        }

        return $this;
    }

    /**
     * Resolves and created a new instance of a desired class.
     *
     * @param string $alias
     * @return mixed
     * @throws SimplesRunTimeError
     */
    public function make(string $alias)
    {
        if (array_key_exists($alias, $this->bindings)) {
            $classOrObject = $this->bindings[$alias];

            if (is_object($classOrObject)) {
                return $classOrObject;
            }

            return $this->makeInstance($classOrObject);
        }

        if (class_exists($alias)) {
            return self::register($alias, $this->makeInstance($alias))->make($alias);
        }
        throw new SimplesRunTimeError("Class '{$alias}' not found");
    }

    /**
     * Created a instance of a desired class.
     *
     * @param $className
     * @return mixed
     */
    protected function makeInstance($className)
    {
        // class reflection
        $reflection = new ReflectionClass($className);
        // get the class constructor
        $constructor = $reflection->getConstructor();

        // if there is no constructor, just create and
        // return a new instance
        if (!$constructor) {
            return $reflection->newInstance();
        }

        // created and returns the new instance passing the
        // resolved parameters
        return $reflection->newInstanceArgs($this->resolveParameters($constructor->getParameters(), []));
    }

    /**
     * Generate a list of values to be used like parameters to one method
     *
     * @param $instance
     * @param $method
     * @param $parameters
     * @param bool $labels
     * @return array
     */
    public function resolveMethodParameters($instance, $method, $parameters, $labels = false)
    {
        // method reflection
        $reflectionMethod = new ReflectionMethod($instance, $method);

        // resolved array of parameters
        return $this->resolveParameters($reflectionMethod->getParameters(), $parameters, $labels);
    }

    /**
     * Generate a list of values to be used like parameters to one function
     *
     * @param $callable
     * @param $parameters
     * @param bool $labels
     * @return array
     */
    public function resolveFunctionParameters($callable, $parameters, $labels = false)
    {
        // method reflection
        $reflectionFunction = new ReflectionFunction($callable);

        // resolved array of parameters
        return $this->resolveParameters($reflectionFunction->getParameters(), $parameters, $labels);
    }

    /**
     * Generate a list of values to be used like parameters to one method or function
     *
     * @param $parameters
     * @param $data
     * @param bool $labels
     * @return array
     *
     */
    private function resolveParameters($parameters, $data, $labels = false)
    {
        $parametersToPass = [];

        /** @var ReflectionParameter $reflectionParameter */
        foreach ($parameters as $reflectionParameter) {
            if (isset($data[$reflectionParameter->getName()])) {
                $parametersToPass[] = $this->parseParameter($reflectionParameter, $data, $labels);
                continue;
            }
            /** @noinspection PhpAssignmentInConditionInspection */
            if ($parameterClassName = $this->extractClassName($reflectionParameter)) {
                $parametersToPass[] = self::make($parameterClassName);
                continue;
            }
            if (count($data)) {
                $parametersToPass[] = $this->parseParameter($reflectionParameter, $data, $labels);
                continue;
            }
            $parametersToPass[] = null;
        }

        return $parametersToPass;
    }

    /**
     * Configure the beste resource to each parameter of one method or function
     *
     * @param ReflectionParameter $reflectionParameter
     * @param $data
     * @param $labels
     * @return null
     */
    private function parseParameter(ReflectionParameter $reflectionParameter, $data, $labels)
    {
        $parameter = null;
        $name = $reflectionParameter->getName();
        $default = $reflectionParameter->getDefaultValue();
        if ($labels && isset($data[$name])) {
            $parameter = $data[$name];
            unset($data[$name]);
        }
        if (!$parameter && isset($data[0])) {
            $parameter = $data[0];
            array_shift($data);
            reset($data);
        }
        if (!$parameter && isset($default)) {
            $parameter = $default;
        }
        return $parameter;
    }

    /**
     * Get the name of class related to a list of parameters
     *
     * @param ReflectionParameter $reflectionParameter
     * @return string
     */
    private function extractClassName(ReflectionParameter $reflectionParameter)
    {
        if (isset($reflectionParameter->getClass()->name)) {
            return $reflectionParameter->getClass()->name;
        }
        return '';
    }
}
