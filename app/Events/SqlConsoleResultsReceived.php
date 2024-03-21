<?php
/*
 * This file is part of RootDB.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * AUTHORS
 * PORQUET Sébastien <sebastien.porquet@ijaz.fr>
 */

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
