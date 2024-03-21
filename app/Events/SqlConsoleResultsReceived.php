<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SqlConsoleResultsReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private User $user;
    private string $stdout;
    private string $stderr;


    public function __construct(string $stdout, string $stderr, User $user)
    {
        $this->stdout = $stdout;
        $this->stderr = $stderr;
        $this->user = $user;
    }

    public function broadcastOn(): Channel|PrivateChannel
    {
        return new PrivateChannel('user.' . $this->user->currentOrganizationLoggedUser->web_socket_session_id);
    }

    public function broadcastWith(): array
    {
        if (strlen($this->stderr) > 0) {
            return [$this->stderr];
        }

        return [$this->stdout];
    }
}
