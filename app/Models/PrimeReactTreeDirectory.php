<?php

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
