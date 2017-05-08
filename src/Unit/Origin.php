<?php

namespace Simples\Unit;

use JsonSerializable;

/**
 * Class Origin
 * @package Simples\Core\Unit
 */
abstract class Origin implements JsonSerializable
{
    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->expose();
    }

    /**
     * @return mixed
     */
    protected abstract function expose();
}
