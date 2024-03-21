<?php

namespace App\Models;

class DBColumn
{
    public string $name;
    public string $comment;
    public string $type;

    public function __construct(string $name, string $comment, string $type)
    {
        $this->name = $name;
        $this->comment = $comment;
        $this->type = $type;
    }
}
