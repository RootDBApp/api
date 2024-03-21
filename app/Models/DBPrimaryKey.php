<?php

namespace App\Models;

class DBPrimaryKey extends DBColumn
{

    public string $table_name = '';

    // DBColumn.name = primary key name
    public function __construct(string $name, string $comment, string $type, string $table_name)
    {
        parent::__construct($name, $comment, $type);
        $this->table_name = $table_name;
    }
}
