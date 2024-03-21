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

use JetBrains\PhpStorm\Pure;

class PrimeReactTreeDirectory extends PrimeReactTree
{

    public int|null $parent_id;

    /**
     * PrimeReactTree constructor.
     * @param string $key
     * @param string $label
     * @param int|null $parent_id
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
    #[Pure] public function __construct(
        string $key,
        string $label,
        int|null $parent_id,
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
            $leaf
        );

        $this->parent_id = $parent_id;
    }
}
