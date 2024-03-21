<?php

namespace App\Events;

use App\Models\User;

class SQLSubQueryStart extends SQLQueries
{
    private int $query_index;
    private int $draft_query_id;

    public function __construct(string $web_socket_session_id, int $draft_query_id, int $query_index)
    {
        parent::__construct($web_socket_session_id);
        $this->draft_query_id = $draft_query_id;
        $this->query_index = $query_index;
    }

    public function broadcastWith(): array
    {
        return [
            "query_index"    => $this->query_index,
            "draft_query_id" => $this->draft_query_id
        ];
    }
}
