<?php

namespace App\Models;

class DBSchema
{
    public string $name;
    /** @var DBTable[] $tables */
    public array $tables;
    /** @var DBView[] $views */
    public array $views;

    public function __construct(string $name, array $tables, array $views)
    {
        $this->name = $name;
        $this->tables = $tables;
        $this->views = $views;
    }
}
