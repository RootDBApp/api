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

class PrimeReactTree
{

    public string $key;
    public string $label;
    public string|null $data;
    public string $icon;
    /** @var PrimeReactTree[]|null $children */
    public array|null $children = null;
    public string|null $style;
    public string|null $className = null;
    public bool $draggable = false;
    public bool $droppable = false;
    public bool $selectable = true;
    public bool $leaf = false;
    public string|null $data_description;

    /**
     * PrimeReactTree constructor.
     * @param string $key
     * @param string $label
     * @param string|null $data
     * @param string $icon
     * @param PrimeReactTree[]|null $children
     * @param string|null $style
     * @param string|null $className
     * @param bool $draggable
     * @param bool $droppable
     * @param bool $selectable
     * @param bool $leaf
     */
    public function __construct(
        string $key,
        string $label,
        string|null $data,
        string $icon,
        array|null $children = null,
        string|null $style = null,
        string|null $className = null,
        bool $draggable = false,
        bool $droppable = false,
        bool $selectable = true,
        bool $leaf = false
    )
    {
        $this->key = $key;
        $this->label = $label;
        $this->data = $data;
        $this->icon = $icon;
        $this->children = $children;
        $this->style = $style;
        $this->className = $className;
        $this->draggable = $draggable;
        $this->droppable = $droppable;
        $this->selectable = $selectable;
        $this->leaf = $leaf;
    }
}
