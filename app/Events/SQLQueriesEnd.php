<?php

namespace App\Events;

use App\Models\User;

class SQLQueriesEnd extends SQLQueries
{
    private int $draft_query_id;

    public function __construct(string $web_socket_session_id, int $draft_query_id)
    {
        parent::__construct($web_socket_session_id);
        $this->draft_query_id = $draft_query_id;
    }

    public function broadcastWith(): array
    {
        return [
            "draft_query_id"    => $this->draft_query_id
        ];
    }
}
