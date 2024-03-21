<?php

namespace App\Events;

use App\Models\User;
use App\Models\VersionInfo;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class VersionInfosEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    private User $user;
    /** @var VersionInfo[] $versionsInfos */
    private array $versionsInfos;

    public function __construct(User $user, array $versionsInfos)
    {
        $this->user = $user;
        $this->versionsInfos = $versionsInfos;
    }

    public function broadcastOn(): PrivateChannel|array
    {
        return new PrivateChannel('user.' . $this->user->currentOrganizationLoggedUser->web_socket_session_id);
    }

    public function broadcastWith(): array
    {
        return $this->versionsInfos;
    }
}
