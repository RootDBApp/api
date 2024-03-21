<?php

namespace App\Models;

class DBForeignKey extends DBColumn
{

    public string $table_name = '';

    // DBColumn.name = constraint name
    public function __construct(string $name, string $comment, string $type, string $table_name)
    {
        parent::__construct($name, $comment, $type);
        $this->table_name = $table_name;
    }
}
