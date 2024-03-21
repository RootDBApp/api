<?php

namespace App\Models;


class AutoCompleteAlias
{
    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
