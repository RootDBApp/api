<?php

namespace App\Models;

/**
 * @property string $value
 * @property int $score
 * @property string $meta
 * @property string $name
 * @property string $caption
 */
class AceBuildCompletion
{
    public string $value;
    public int $score;
    public string $meta;
    public string $name;
    public string $caption;

    /**
     * AceBuildCompletion constructor.
     * @param string $value
     * @param int $score
     * @param string $meta
     * @param string $name
     * @param string $caption
     */
    public function __construct(string $value, int $score, string $meta, string $name, string $caption)
    {
        $this->value = $value;
        $this->score = $score;
        $this->meta = $meta;
        $this->name = $name;
        $this->caption = $caption;
    }
}
