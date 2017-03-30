<?php

// show all errors
error_reporting(E_ALL);

// system settings
ini_set('display_errors', env('ERRORS_DISPLAY', 'On'));
ini_set('log_errors', env('ERRORS_DISPLAY', 'On'));
ini_set('track_errors', env('ERRORS_DISPLAY', 'Off'));
ini_set('html_errors', env('ERRORS_DISPLAY', 'Off'));

// native types
const TYPE_BOOLEAN = 'boolean';
const TYPE_INTEGER = 'integer';
const TYPE_FLOAT = 'float';
const TYPE_STRING = 'string';
const TYPE_ARRAY = 'array';
const TYPE_OBJECT = 'object';
const TYPE_RESOURCE = 'resource';
const TYPE_NULL = 'null';
const TYPE_UNKNOWN_TYPE = 'unknown type';

// custom types
const TYPE_DATE = 'date';

const __AND__ = 'AND';
const __OR__ = 'OR';

// used to compose path do generator
define('TEMPLATE_DIR', 'kernel/resources/templates');

if (!function_exists('error_handler')) {
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
    set_error_handler("error_handler");
}
