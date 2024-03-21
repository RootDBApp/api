<?php

namespace App\Models;

class DBIndex extends DBColumn
{

    public string $index_type = '';
    public string $table_name = '';

    // DBColumn.name = index name
    public function __construct(string $name, string $comment, string $type, string $index_type, string $table_name)
    {
        parent::__construct($name, $comment, $type);
        $this->index_type = $index_type;
        $this->table_name = $table_name;
    }
}
