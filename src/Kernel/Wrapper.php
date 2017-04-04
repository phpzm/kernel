<?php

namespace Simples\Kernel;

use Simples\Helper\JSON;

/**
 * Class Wrapper
 * @package Simples\Kernel
 */
abstract class Wrapper
{
    /**
     * @var array
     */
    private static $messages = [];

    /**
     * @param string $message
     * @param bool $trace (false)
     */
    public static function warning($message, bool $trace = false)
    {
        self::message('warning', $message, $trace);
    }

    /**
     * @param string $message
     * @param bool $trace (false)
     */
    public static function info($message, bool $trace = false)
    {
        self::message('info', $message, $trace);
    }

    /**
     * @param string $message
     * @param bool $trace (false)
     */
    public static function buffer($message, bool $trace = false)
    {
        self::message('buffer', $message, $trace);
    }

    /**
     * @param array ...$data
     */
    public static function log(...$data)
    {
        self::message('log', $data, true);
    }

    /**
     * @param string $type
     * @param string $message
     * @param bool $trace
     */
    public static function message($type, $message, bool $trace = false)
    {
        self::$messages[] = [
            'type' => $type,
            'message' => gettype($message) === TYPE_STRING ? $message : JSON::encode($message),
            'trace' => $trace ? self::trace() : false
        ];
    }

    /**
     * @return array
     */
    public static function messages()
    {
        return self::$messages;
    }

    /**
     * @return array
     */
    protected static function trace()
    {
        $stack = App::beautifulTrace(debug_backtrace());

        return array_slice($stack, 3);
    }
}
