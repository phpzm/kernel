<?php

namespace Simples\Kernel;

/**
 * Class Base
 * @package Simples\Kernel
 */
abstract class Base
{
    /**
     * On the fly configs of App gets on config folder
     *
     * @var array
     */
    protected static $configs = [];

    /**
     * Options of App behavior
     *
     * @var array
     */
    protected static $options = [];

    /**
     * Default properties of options
     *
     * @var array
     */
    protected static $default = [];

    /**
     * Setup the options to App settings
     *
     * @param array $options
     * @return array
     */
    protected static function setup(array $options = null): array
    {
        if ($options) {
            static::$options = array_merge(static::$default, $options);
        }
        return static::$options;
    }

    /**
     * Management to options of app
     *
     * @param string $key (null) The id of option to get or set
     * @param string $value (null) The value to set
     * @return mixed Returns the entire options if there is no $key & no $value, else return the respective $option
     */
    public static function options($key = null, $value = null)
    {
        $options = static::setup();
        if (!$key) {
            return $options;
        }
        if (!$value) {
            return $options[$key] ?? null;
        }
        static::$options[$key] = $value;

        return static::$options;
    }

    /**
     * Interface to get config values
     *
     * @param string $path The path of config ex.: "app.name", equivalent to Name of App
     * @return mixed Instance of stdClass with the all properties or the value available in path
     */
    public static function config($path)
    {
        $peaces = explode('.', $path);
        $name = $peaces[0];
        array_shift($peaces);

        $config = null;
        if (isset(static::$configs[$name])) {
            $config = static::$configs[$name];
        }
        if (!$config) {
            $filename = path(true, "config/{$name}.php");
            if (file_exists($filename)) {
                /** @noinspection PhpIncludeInspection */
                $config = (object)require $filename;
                static::$configs[$name] = $config;
            }
        }
        if (count($peaces) === 0) {
            return $config;
        }

        return search((array)$config, $peaces);
    }
}