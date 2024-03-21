<?php

namespace App\Events;

use App\Models\User;

class SQLQueriesResultsReceived extends SQLQueries
{
    protected array $results;
    protected int $query_index;
    protected int $draft_query_id;

    public function __construct(string $web_socket_session_id, array $results, int $draft_query_id, int $query_index)
    {
        parent::__construct($web_socket_session_id);
        $this->results = $results;
        $this->query_index = $query_index;
        $this->draft_query_id = $draft_query_id;
    }

    public function broadcastWith(): array
    {
        return [
            "query_index"    => $this->query_index,
            "draft_query_id" => $this->draft_query_id,
            "results"        => [
                'stdout' => $this->results,
                'stderr' => []
            ]
        ];
    }
}
