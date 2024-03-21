<?php

namespace App\Events;

use App\Models\User;

class SQLQueriesErrorReceived extends SQLQueriesResultsReceived
{
    private array $errors;

    public function __construct(string $web_socket_session_id, array $results, int $draft_query_id, int $query_index, array $errors)
    {
        parent::__construct($web_socket_session_id, $results, $draft_query_id, $query_index);
        $this->errors = $errors;
    }

    public function broadcastWith(): array
    {
        return [
            "query_index"    => $this->query_index,
            "draft_query_id" => $this->draft_query_id,
            "results"        => [
                'stdout' => [],
                'stderr' => $this->errors
            ]
        ];
    }
}
