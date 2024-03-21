<?php

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
