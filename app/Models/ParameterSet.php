<?php

namespace App\Models;

class ParameterSet
{

    public string $name;
    public string|int $value;

    public function __construct(string $name, int|string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

}
