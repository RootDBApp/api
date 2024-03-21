<?php

namespace App\Models;

class CacheJobParameterNameValues
{
    public readonly string $variable_name;
    /** @var int[]|string[] $values */
    public readonly array $values;

    /**
     * @param string $variable_name
     * @param int[]|string[] $values
     */
    public function __construct(string $variable_name, array $values)
    {
        $this->variable_name = $variable_name;
        $this->values = $values;
    }
}
