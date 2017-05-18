<?php

namespace Simples\Kernel;

/**
 * Class Middleware
 * @package Simples\Kernel
 */
abstract class Middleware
{
    /**
     * The name of middleware, must be a string without spaces and special chars
     * preg_replace('/[^a-z0-9]/i', '_', $alias)
     *
     * @var string
     */
    protected $alias = '';

    /**
     * Get the name of middleware to be used in pipes
     *
     * @return string
     */
    final public function alias(): string
    {
        return $this->alias;
    }

}
