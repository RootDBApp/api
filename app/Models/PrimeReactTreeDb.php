<?php

namespace App\Models;

class PrimeReactTreeDb extends PrimeReactTree
{
    public const VIEW = 5;
    public const TABLE = 2;
    public const FOREIGN_KEY = 2;
    public const TABLE_SCHEMA = 1;
    public const COLUMN = 3;
    public const PRIMARY_KEY = 3;
    public const SIMPLE = 1;
    public const INDEX = 4;
    public const VIEWS_DIRECTORY = 6;

    public int|null $column_type;
    public int $label_type;
    public string|null $data_description;

    public function __construct(
        string      $key,
        string      $label,
        int|null    $column_type,
        int         $label_type,
        string|null $data,
        string|null $data_description,
        string      $icon,
        array|null  $children = null,
        string|null $style = null,
        string|null $className = null,
        bool        $draggable = false,
        bool        $droppable = false,
        bool        $selectable = true,
        bool        $leaf = false,
    )
    {
        parent::__construct(
            $key,
            $label,
            $data,
            $icon,
            $children,
            $style,
            $className,
            $draggable,
            $droppable,
            $selectable,
            $leaf,
        );

        $this->data_description = $data_description;
        $this->column_type = $column_type;
        $this->label_type = $label_type;
    }
}
