<?php

namespace Simples\Kernel;

use Simples\Error\SimplesRunTimeError;

/**
 * Class App
 * @package Simples\Kernel
 */
class App
{
    /**
     * On the fly configs of App gets on config folder
     *
     * @var array
     */
    private static $CONFIGS = [];

    /**
     * Options of App behavior
     *
     * @var array
     */
    private static $OPTIONS;

    /**
     * Defines o log level of App
     *
     * @var boolean
     */
    private static $logging;

    /**
     * App constructor
     *
     * Create a instance of App Handler
     *
     * @param array $options ([
     *      'root' => string,
     *      'lang' => array,
     *      'labels' => boolean,
     *      'headers' => array,
     *      'type' => string
     *      'separator' => string
     *      'strict' => boolean
     *  ])
     */
    public function __construct($options)
    {
        static::start($options);
    }

    /**
     * @param array $options
     * @return array
     */
    private static function start(array $options = [])
    {
        if (!self::$OPTIONS) {
            $default = [
                'root' => dirname(__DIR__, 5),
                'lang' => [
                    'default' => 'en', 'fallback' => 'en'
                ],
                'labels' => true,
                'headers' => [],
                'type' => 'html',
                'separator' => '@',
                'filter' => '~>',
                'strict' => false
            ];
            self::$OPTIONS = array_merge($default, $options);
        }
        return self::$OPTIONS;
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
        self::start();
        if ($key) {
            if (!$value) {
                return self::$OPTIONS[$key] ?? null;
            }
            self::$OPTIONS[$key] = $value;
        }
        return self::$OPTIONS;
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
        if (isset(self::$CONFIGS[$name])) {
            $config = self::$CONFIGS[$name];
        }
        if (!$config) {
            $filename = path(true, "config/{$name}.php");
            if (file_exists($filename)) {
                /** @noinspection PhpIncludeInspection */
                $config = (object) require $filename;
                self::$CONFIGS[$name] = $config;
            }
        }
        if (count($peaces) === 0) {
            return $config;
        }

        return search((array)$config, $peaces);
    }

    /**
     * Get value default created in defaults config to some class
     * @param string $class
     * @param string $property
     * @return mixed
     */
    public static function defaults(string $class, string $property)
    {
        return static::config("defaults.{$class}.{$property}");
    }

    /**
     * @SuppressWarnings("BooleanArgumentFlag")
     *
     * @param array $trace
     * @param bool $filter
     * @return array
     */
    public static function beautifulTrace(array $trace, bool $filter = true): array
    {
        $stack = [];
        foreach ($trace as $value) {
            $trace = off($value, 'function');
            if ($trace === 'call_user_func_array') {
                continue;
            }
            $class = off($value, 'class');
            $function = off($value, 'function');
            if ($filter && strpos($class, 'Simples\\Core\\Kernel') === 0) {
                continue;
            }
            if ($class && $function) {
                $trace = $class . App::options('separator') . $function;
            }
            $stack[] = $trace;
        }
        return $stack;
    }

    /**
     * Configure the logging level inf App
     *
     * @param bool $logging (null)
     * @return bool|null
     */
    public static function log(bool $logging = null)
    {
        if (!is_null($logging)) {
            self::$logging = $logging;
        }
        return self::$logging;
    }

    /**
     * @SuppressWarnings("BooleanArgumentFlag")
     * Used to catch http requests and handle response to their
     *
     * @param bool $output (true) Define if the method will generate one output with the response
     * @return mixed The match response for requested resource
     * @throws SimplesRunTimeError Generated when is not possible commit the changes
     */
    public function http($output = true)
    {
        if (class_exists('\\Simples\\Http\\Kernel\\App')) {
            return \Simples\Http\Kernel\App::handler($output);
        }
        throw new SimplesRunTimeError("App can't handler Http without the package `phpzm/http`");
    }

    /**
     * Handler to cli services, provide a interface to access services
     *
     * @param array $service The requested service
     * @throws SimplesRunTimeError
     */
    public function cli(array $service)
    {
        if (class_exists('\\Simples\\Console\\Kernel\\App')) {
            \Simples\Console\Kernel\App::handler($service);
        }
        throw new SimplesRunTimeError("App can't handler Console without the package `phpzm/console`");
    }
}
