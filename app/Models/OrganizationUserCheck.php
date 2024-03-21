<?php

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
