<?php

namespace App\Models;


class CacheJobDataViewStats
{

    public readonly int $num_results;
    public readonly int $item_size_in_bytes;

    public function __construct(int $num_results, int $item_size_in_bytes)
    {
        $this->num_results = $num_results;
        $this->item_size_in_bytes = $item_size_in_bytes;
    }


}
