<?php

namespace Simples\Kernel;

use Simples\Error\SimplesAlreadyRegisteredError;
use Simples\Error\SimplesRunTimeError;
use Simples\Helper\File;

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
    private static $configs = [];

    /**
     * Options of App behavior
     *
     * @var array
     */
    private static $options;

    /**
     * Defines o log level of App
     *
     * @var boolean
     */
    private static $logging;

    /**
     * Pipe of middlewares to be solved
     * @var array
     */
    private $pipe = [];

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
        static::setup($options);
    }

    /**
     * Setup the options to App settings
     *
     * @param array $options
     * @return array
     */
    private static function setup(array $options = null): array
    {
        if ($options) {
            $default = [
                'root' => dirname(__DIR__, 5),
                'lang' => [
                    'default' => 'en',
                    'fallback' => 'en'
                ],
                'labels' => true,
                'headers' => [],
                'type' => 'html',
                'separator' => '@',
                'filter' => '~>',
                'avoid' => 7,
                'strict' => false
            ];
            static::$options = array_merge($default, $options);
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
     * @param array $trace
     * @return array
     */
    public static function beautifulTrace(array $trace): array
    {
        $stack = [];
        foreach ($trace as $value) {
            $trace = off($value, 'function');
            if ($trace === 'call_user_func_array') {
                continue;
            }
            $class = off($value, 'class');
            $function = off($value, 'function');
            if ($class && $function) {
                $trace = str_replace('\\', '/', $class) . App::options('separator') . $function;
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
    public static function logging(bool $logging = null)
    {
        if (!is_null($logging)) {
            static::$logging = $logging;
        }
        return static::$logging;
    }

    /**
     * Create a file with data to be analysed
     *
     * @param array ...$data
     * @return int
     */
    public static function log(...$data)
    {
        $filename = static::options('root') . '/storage/log/access';
        if (is_array($data) && count($data) === 1) {
            $data = $data[0];
        }
        return File::write($filename, $data, true);
    }

    /**
     * Add middleware's to pipe
     *
     * @param Middleware $middleware
     * @param string $alias
     * @return App
     * @throws SimplesAlreadyRegisteredError
     */
    public function pipe(Middleware $middleware, string $alias = ''): App
    {
        $alias = $alias ? $alias : $middleware->alias();
        if (isset($this->pipe[$alias])) {
            throw new SimplesAlreadyRegisteredError("The middleware `{$alias}` is already registered");
        }
        $this->pipe[$alias] = $middleware;

        return $this;
    }

    /**
     * Used to catch http requests and handle response to their
     *
     * @param bool $output (true) Define if the method will generate one output with the response
     * @return mixed The match response for requested resource
     * @throws SimplesRunTimeError Generated when is not possible commit the changes
     */
    public function http($output = true)
    {
        if (!class_exists('\\Simples\\Http\\Kernel\\App')) {
            throw new SimplesRunTimeError("App can't handler Http without the package `phpzm/http`");
        }
        return \Simples\Http\Kernel\App::handle($this->pipe, $output);
    }

    /**
     * Handler to cli services, provide a interface to access services
     *
     * @param array $service The requested service
     * @throws SimplesRunTimeError
     */
    public function cli(array $service)
    {
        if (!class_exists('\\Simples\\Console\\Kernel\\App')) {
            throw new SimplesRunTimeError("App can't handler Console without the package `phpzm/console`");
        }
        \Simples\Console\Kernel\App::handle($service);
    }
}
