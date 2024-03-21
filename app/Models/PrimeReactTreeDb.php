<?php
/*
 * This file is part of RootDB.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * AUTHORS
 * PORQUET Sébastien <sebastien.porquet@ijaz.fr>
 */

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
