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

namespace App\Models;

use Illuminate\Auth\Access\Response;

class OrganizationUserCheck
{
    public bool $result = false;
    public OrganizationUser|null $organizationUser = null;
    public Response|null $response = null;

    /**
     * @param bool $result
     * @param OrganizationUser|null $organizationUser
     * @param Response|null $response
     */
    public function __construct(bool $result, ?OrganizationUser $organizationUser, ?Response $response)
    {
        $this->result = $result;
        $this->organizationUser = $organizationUser;
        $this->response = $response;
    }
}
