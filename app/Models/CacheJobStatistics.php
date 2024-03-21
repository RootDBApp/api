<?php


namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;

class CacheJobStatistics implements Arrayable
{
    public readonly int $num_parameter_sets;
    public readonly int $cache_size_b;

    public function __construct(int $num_parameter_sets, string $cache_size_b)
    {
        $this->num_parameter_sets = $num_parameter_sets;
        $this->cache_size_b = $cache_size_b;
    }

    public function toArray(): array
    {
        return [
            'num_parameter_sets' => $this->num_parameter_sets,
            'cache_size_b'       => $this->cache_size_b,
        ];
    }
}
