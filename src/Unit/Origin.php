<?php

namespace Simples\Unit;

/**
 * Class Origin
 * @package Simples\Core\Unit
 */
class Origin
{
    /**
     * @return string
     */
    public function __toString()
    {
        $properties = [];
        foreach ($this as $key => $value) {
            $properties = [$key => $value];
        }
        return json_encode($properties);
    }
}
