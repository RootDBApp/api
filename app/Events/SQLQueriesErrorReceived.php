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
