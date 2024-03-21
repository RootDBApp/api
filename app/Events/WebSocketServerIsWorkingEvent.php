<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class WebSocketServerIsWorkingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function broadcastOn(): PrivateChannel|array
    {
        return new PrivateChannel('user.' . $this->user->currentOrganizationLoggedUser->web_socket_session_id);
    }
}
