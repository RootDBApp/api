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

class SQLQueriesStart extends SQLQueries
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
