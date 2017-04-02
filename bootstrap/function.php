<?php

use Simples\Error\SimplesRunTimeError;
use Simples\Kernel\App;

/**
 * @param string $index
 * @return mixed
 */
function server(string $index)
{
    return filter(INPUT_SERVER, $index);
}

/**
 * @param string $index
 * @return mixed
 */
function post(string $index)
{
    return filter(INPUT_POST, $index);
}

/**
 * @param string $index
 * @return mixed
 */
function get(string $index)
{
    return filter(INPUT_GET, $index);
}

/**
 * @param int $source
 * @param string $index
 * @return mixed
 */
function filter(int $source, string $index)
{
    return filter_input($source, $index);
}

/**
 * @param string $property
 * @param mixed $default (null)
 * @return string
 */
function env($property, $default = null)
{
    $filename = path(true, '.env');
    if (file_exists($filename) && is_file($filename)) {
        $properties = parse_ini_file($filename);
        if (is_array($properties)) {
            return off($properties, $property);
        }
    }
    return $default;
}


/**
 * @param string $root
 * @return string
 */
function path($root)
{
    $args = func_get_args();
    $peaces = [];
    if (is_bool($root)) {
        array_shift($args);
        if ($root) {
            $dir = \Simples\Kernel\App::options('root');
            if (!$dir) {
                $dir = dirname(__DIR__, 4);
            }
            $peaces = [$dir];
        }
    }
    $path = array_merge($peaces, $args);

    return implode(DIRECTORY_SEPARATOR, $path);
}

/**
 * @param string $path
 * @return string
 */
function storage($path)
{
    return path(true, 'storage', $path);
}

/**
 * @param string $path
 * @return string
 */
function resources($path)
{
    return path(true, 'app/resources', $path);
}

/**
 * @param mixed $value
 */
function out($value)
{
    print parse($value);
}

/**
 * @param mixed $value
 * @return string
 */
function parse($value): string
{
    switch (gettype($value)) {
        case TYPE_BOOLEAN:
            return $value ? 'true' : 'false';
            break;
        case TYPE_INTEGER:
        case TYPE_FLOAT:
        case TYPE_STRING:
            return trim($value);
            break;
        case TYPE_ARRAY:
        case TYPE_OBJECT:
        case TYPE_RESOURCE:
            return json_encode($value);
        // case TYPE_NULL:
        // case TYPE_UNKNOWN_TYPE:
        default:
            return null;
    }
}

/**
 * @param array ...$arguments
 * @return mixed
 * @throws SimplesRunTimeError
 */
function coalesce(...$arguments)
{
    foreach ($arguments as $argument) {
        if (!is_null($argument)) {
            return $argument;
        }
    }
    throw new SimplesRunTimeError("Can't resolve coalesce options", $arguments);
}

/**
 * @param mixed $value
 * @param string|int $property (null)
 * @param mixed $default (null)
 *
 * @return mixed
 */
function off($value, $property = null, $default = null)
{
    if (is_null($property)) {
        return $default;
    }
    if (!$value) {
        return $default;
    }
    if (is_array($value)) {
        return search($value, $property, $default);
    }
    /** @noinspection PhpVariableVariableInspection */
    if ($value && is_object($value) && isset($value->$property)) {
        /** @noinspection PhpVariableVariableInspection */
        return $value->$property;
    }
    return $default;
}

/**
 * @param array ...$arguments
 * @SuppressWarnings("ExitExpression")
 */
function stop(...$arguments)
{
    ob_start();
    echo json_encode($arguments);
    $contents = ob_get_contents();
    ob_end_clean();
    out($contents);
    die;
}

/**
 * @param string $name
 * @return mixed
 */
function config($name)
{
    return \Simples\Kernel\App::config($name);
}

/**
 * @param string $name
 * @return string
 */
function headerify($name)
{
    return str_replace(' ', '-', ucwords(strtolower(str_replace(['_', '-'], ' ', $name))));
}

/**
 * @param string $from
 * @param string $to
 * @param string $subject
 * @param bool $quote (false)
 * @return mixed
 */
function str_replace_first($from, $to, $subject, $quote = false)
{
    if ($quote) {
        $from = '/' . preg_quote($from, '/') . '/';
    }

    return preg_replace($from, $to, $subject, 1);
}

/**
 * @param bool $brackets
 * @return string
 */
function guid($brackets = false)
{
    mt_srand((double)microtime() * 10000);

    $char = strtoupper(md5(uniqid(rand(), true)));
    $hyphen = chr(45);
    $uuid = substr($char, 0, 8) . $hyphen . substr($char, 8, 4) . $hyphen . substr($char, 12, 4) . $hyphen .
        substr($char, 16, 4) . $hyphen . substr($char, 20, 12);
    if ($brackets) {
        $uuid = chr(123) . $uuid . chr(125);
    }

    return $uuid;
}

/**
 * @param mixed $var
 * @return bool
 */
function is_iterator($var)
{
    return (is_array($var) || $var instanceof Traversable);
}

/**
 * @param Throwable $throw
 * @return string
 */
function throw_format(Throwable $throw)
{
    return "[{$throw->getMessage()}] ON [{$throw->getFile()}] AT [{$throw->getLine()}]";
}

/**
 * @param Throwable $error
 * @return array
 */
function error_format(Throwable $error): array
{
    $trace = $error->getTraceAsString();
    //$trace = App::beautifulTrace($error->getTrace());
    return [
        'error' => [
            'fail' => get_class($error),
            'details' => $error instanceof SimplesRunTimeError ? $error->getDetails() : [],
            // 'trace' => array_slice($trace, 1, count($trace) - App::options('avoid'))
            'trace' => array_map(function ($value) {
                return str_replace('/', '\\', str_replace(App::options('root'), '', $value));
            }, explode(PHP_EOL, $trace))
        ]
    ];
}

/**
 * @param array $context
 * @param array|string $path
 * @param mixed $default (null)
 * @return mixed|null
 */
function search(array $context, $path, $default = null)
{
    if (!is_array($path)) {
        $path = explode('.', $path);
    }
    foreach ($path as $piece) {
        if (!is_array($context) || !array_key_exists($piece, $context)) {
            return $default;
        }
        $context = $context[$piece];
    }
    return $context;
}

/**
 * @param string $prompt
 * @param string $options
 * @return string
 */
function read(string $prompt = '$ ', string $options = ''): string
{
    if ($options) {
        $prompt = "{$prompt} {$options}\$ ";
    }
    $reader = function () use ($prompt) {
        return readline("{$prompt}");
    };
    if (PHP_OS === 'WINNT') {
        $reader = function () use ($prompt) {
            echo $prompt;
            return stream_get_line(STDIN, 1024, PHP_EOL);
        };
    }
    $line = $reader();
    readline_add_history($line);

    return trim($line);
}

/**
 * @param string $path
 * @return string
 */
function clearpath(string $path): string
{
    return implode('/', array_filter(explode('/', $path), function ($value) {
        if (!in_array($value, ['..', '.'])) {
            return $value;
        }
        return null;
    }));
}

/**
 * @param mixed $output
 * @param mixed $optional (null)
 * @return mixed
 */
function test($output, $optional = null)
{
    if (env('TEST_MODE')) {
        return $output;
    }
    return $optional;
}

/**
 * @param array $argv
 * @return array
 */
function argv(array $argv): array
{
    array_shift($argv);

    $parameters = [];
    foreach ($argv as $arg) {
        $parameter = explode('=', $arg);
        $value = $parameter[0];
        if (count($parameter) === 2) {
            $parameters[$value] = $parameter[1];
            continue;
        }
        $parameters[] = $value;
    }
    return $parameters;
}

/**
 * @param string $camelCase
 * @return string
 */
function dasherize(string $camelCase): string
{
    $args = '$matches';
    $code = 'return \'-\' . strtolower($matches[1]);';
    $dashes = preg_replace_callback('/([A-Z])/', create_function($args, $code), $camelCase);
    return substr($dashes, 1);
}

/**
 * @param string $dashes
 * @param bool $first
 * @return mixed
 */
function camelize(string $dashes, $first = true): string
{
    $args = '$matches';
    $code = 'return strtoupper($matches[1]);';
    $camelCase = preg_replace_callback('/-(.)/', create_function($args, $code), $dashes);
    $case = 'strtoupper';
    if (!$first) {
        $case = 'strtolower';
    }
    $camelCase[0] = $case($camelCase[0]);
    return (string)$camelCase;
}

/**
 * Extract the short name of class
 *   Ex.: (Namespace\Class) => Class
 * @param string $class
 * @return string
 */
function get_class_short_name(string $class)
{
    return basename(str_replace('\\', '/', $class));
}

/**
 * @param $code
 * @param $message
 * @param $file
 * @param $line
 * @throws ErrorException
 */
function error_handler($code, $message, $file, $line)
{
    throw new ErrorException($message, $code, 1, $file, $line);
}
