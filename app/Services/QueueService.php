<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QueueService
{
    public const QUEUE_QUERY_NUM_TAG = 'queue_tag';
    public const QUEUE_QUERY_NUM_KEY = 'queue_query_num_key';
    public const QUEUE_TTL = 7200;

    private int $queue_query_connections;
    private int $queue_query_num;

    public function __construct()
    {
        $this->queue_query_connections = config('queue_query_connections', 10);

        /** @var int|null $queue_query_num */
        $queue_query_num = Cache::tags([self::QUEUE_QUERY_NUM_TAG])->get(self::QUEUE_QUERY_NUM_KEY);

        $this->queue_query_num = is_null($queue_query_num) ? 0 : $queue_query_num;
    }

    public function getQueueQueryName(): string
    {
        if ($this->queue_query_num >= $this->queue_query_connections) {

            $this->queue_query_num = 1;
        } else {

            $this->queue_query_num++;
        }

        Log::debug('queue_query_name used :', ['queue_query_' . $this->queue_query_num]);
        Cache::tags([self::QUEUE_QUERY_NUM_TAG])->put(
            self::QUEUE_QUERY_NUM_KEY,
            $this->queue_query_num,
            self::QUEUE_TTL
        );

        return 'queue_query_' . $this->queue_query_num;
    }
}
