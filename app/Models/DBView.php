<?php

namespace App\Models;

class DBView
{
    public string $name;
    /** @var DBColumn[] $columns */
    public array $columns;


    public function __construct(string $name, array $columns)
    {
        $this->name = $name;
        $this->columns = $columns;
    }
}
