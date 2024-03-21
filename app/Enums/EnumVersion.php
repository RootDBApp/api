<?php

namespace App\Enums;

enum EnumVersion: string
{
    case rootdb = 'rootdb';
    case API = 'rootdb-api';
    case Frontend = 'rootdb-frontend';
}
