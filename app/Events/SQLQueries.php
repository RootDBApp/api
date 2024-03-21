<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SQLQueries implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected string $web_socket_session_id;

    public function __construct(string $web_socket_session_id)
    {
        $this->web_socket_session_id = $web_socket_session_id;
    }

    public function broadcastOn(): Channel
    {
        Log::debug('glop', ['user.' . $this->web_socket_session_id]);
        return new PrivateChannel('user.' . $this->web_socket_session_id);
    }
}
